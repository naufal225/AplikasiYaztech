<?php

namespace App\Http\Controllers\ApproverController;

use App\Events\LeaveLevelAdvanced;
use App\Exports\LeavesExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\Leave;
use App\Models\User;
use App\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class LeaveController extends Controller
{
    public function index(Request $request)
    {

        // Query for user's own requests (all statuses)
        $ownRequestsQuery = Leave::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = Leave::with(['employee', 'approver'])->forLeader(Auth::id())
            ->where(function ($q) {
                $q->where('employee_id', '!=', Auth::id())
                    ->orWhere(function ($subQ) {
                        $subQ->where('employee_id', Auth::id())
                            ->where('status_1', 'approved');
                    });
            })
            ->orderBy('created_at', 'desc');

        // Apply filters to both queries
        if ($request->filled('status')) {
            $statusFilter = function ($query) use ($request) {
                switch ($request->status) {
                    case 'approved':
                        $query->where('status_1', 'approved');
                        break;
                    case 'rejected':
                        $query->where('status_1', 'rejected');
                        break;
                    case 'pending':
                        $query->where('status_1', 'pending');
                        break;
                }
            };

            $ownRequestsQuery->where($statusFilter);
            $allUsersQuery->where($statusFilter);
        }

        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta');
            $ownRequestsQuery->where('created_at', '>=', $fromDate);
            $allUsersQuery->where('created_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta');
            $ownRequestsQuery->where('created_at', '<=', $toDate);
            $allUsersQuery->where('created_at', '<=', $toDate);
        }

        $ownRequests = $ownRequestsQuery->paginate(10, ['*'], 'own_page');
        $allUsersRequests = $allUsersQuery->paginate(10, ['*'], 'all_page');


        $sisaCuti = (int) env('CUTI_TAHUNAN', 20)
            - (int) Leave::where('employee_id', Auth::id())
                ->where('status_1', 'approved')
                ->whereYear('date_start', now()->year)
                ->select(DB::raw('SUM(DATEDIFF(date_end, date_start) + 1) as total_days'))
                ->value('total_days');

        $totalRequests = Leave::count();
        $pendingRequests = Leave::where('status_1', 'pending')->count();
        $approvedRequests = Leave::where('status_1', 'approved')->count();
        $rejectedRequests = Leave::where('status_1', 'rejected')->count();

        $manager = User::where('role', Roles::Manager->value)->first();

        Leave::whereNull('seen_by_approver_at')
            ->whereHas('employee', fn($q) => $q->where('division_id', auth()->user()->division_id))
            ->update(['seen_by_approver_at' => now()]);

        return view('approver.leave-request.index', compact(
            'ownRequests',
            'allUsersRequests',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'sisaCuti',
            'manager'
        ));
    }


    public function show(Leave $leave)
    {
        if ($leave->employee->division->leader->id !== Auth::id()) {
            return abort(403, 'Unauthorized');
        }

        $leave->load(['employee', 'approver']);
        return view('approver.leave-request.show', compact('leave'));
    }

    public function create()
    {
        return view('approver.leave-request.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        ]);

        if (!Auth::user()->division_id) {
            return back()->with('error', 'You are not in a division. Please contact your administrator.');
        }

        DB::transaction(function () use ($request) {
            $leave = new Leave();
            $leave->employee_id = Auth::id();
            $leave->date_start = $request->date_start;
            $leave->date_end = $request->date_end;
            $leave->reason = $request->reason;
            $leave->status_1 = 'pending';
            $leave->save();

            $tokenRaw = null;
            // --- Email ke approver ---
            $manager = User::where('role', Roles::Manager->value)->first();
            if ($manager) {
                $token = Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($leave),   // App\Models\Leave
                    'model_id' => $leave->id,
                    'approver_user_id' => $manager->id,
                    'level' => 2,
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $token), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);
            }

            // pastikan broadcast SETELAH commit
            DB::afterCommit(function () use ($leave, $tokenRaw, $manager) {
                $fresh = $leave->fresh(); // ambil ulang (punya created_at dll)

                // event(new \App\Events\LeaveSubmitted($fresh, Auth::user()->division_id));
                event(new \App\Events\LeaveLevelAdvanced($fresh, Auth::user()->division_id, 'manager'));

                if (!$fresh || !$fresh->approver || !$tokenRaw) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $tokenRaw); // pastikan route param sesuai

                // Gunakan queue
                Mail::to($manager->email)->queue(
                    new \App\Mail\SendMessage(
                        namaPengaju: $leave->employee->name,
                        namaApprover: $manager->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: $leave->employee->email
                    )
                );
            });

        });

        return redirect()->route('approver.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function edit(Leave $leave)
    {
        return view('approver.leave-request.update', compact('leave'));
    }

    public function update(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'status_1' => 'nullable|string|in:approved,rejected',
            'status_2' => 'nullable|string|in:approved,rejected',
            'note_1' => 'nullable|string|min:3|max:100',
            'note_2' => 'nullable|string|min:3|max:100',
        ], [
            'status_1.in' => 'Status 1 hanya boleh berisi: approved atau rejected.',
            'status_2.in' => 'Status 2 hanya boleh berisi: approved atau rejected.',
            'note_1.string' => 'Catatan 1 harus berupa teks.',
            'note_1.min' => 'Catatan 1 minimal harus berisi 3 karakter.',
            'note_1.max' => 'Catatan 1 maksimal hanya boleh 100 karakter.',
            'note_2.string' => 'Catatan 2 harus berupa teks.',
            'note_2.min' => 'Catatan 2 minimal harus berisi 3 karakter.',
            'note_2.max' => 'Catatan 2 maksimal hanya boleh 100 karakter.',
        ]);


        // Cegah update dua status sekaligus
        if ($request->filled('status_1') && $request->filled('status_2')) {
            return back()->withErrors(['status' => 'Hanya boleh mengubah salah satu status dalam satu waktu.']);
        }

        $statusMessage = '';

        // === STATUS 1 ===
        if ($request->filled('status_1')) {

            if ($leave->status_1 !== 'pending') {
                return back()->withErrors(['status_1' => 'Status 1 sudah final dan tidak dapat diubah.']);
            }

            // Jika direject, cascade ke status_2 juga
            // Jika direject, cascade ke status_2 juga
            if ($validated['status_1'] === 'rejected') {
                $leave->update([
                    'status_1' => 'rejected',
                    'note_1' => $validated['note_1'] ?? NULL,
                    'status_2' => 'rejected', // ikut rejected juga
                    'note_2' => $validated['note_2'] ?? NULL,
                ]);
            } else {
                // approved → kirim notifikasi ke manager
                $leave->update([
                    'status_1' => 'approved',
                    'note_1' => $validated['note_1'] ?? NULL,
                ]);

                event(new LeaveLevelAdvanced($leave, Auth::user()->division_id, 'manager'));

                $manager = User::where('role', Roles::Manager->value)->first();
                if ($manager) {
                    $token = Str::random(48);
                    ApprovalLink::create([
                        'model_type' => get_class($leave),   // App\Models\Leave
                        'model_id' => $leave->id,
                        'approver_user_id' => $manager->id,
                        'level' => 2,
                        'scope' => 'both',             // boleh approve & reject
                        'token' => hash('sha256', $token), // simpan hash, kirim raw
                        'expires_at' => now()->addDays(3),  // masa berlaku
                    ]);
                    $link = route('public.approval.show', $token);

                    // Gunakan queue
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: $leave->employee->name,
                            namaApprover: $manager->name,
                            linkTanggapan: $link,
                            emailPengaju: $leave->employee->email
                        )
                    );
                }
            }

            $statusMessage = $validated['status_1'];
        }

        return redirect()
            ->route('approver.leaves.index')
            ->with('success', "Leave request {$statusMessage} successfully.");
    }


    public function destroy(Leave $leave)
    {
        // Check if the user has permission to delete this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow deleting if the leave is still pending
        if (($leave->status_1 !== 'pending')) {
            return redirect()->route('approver.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        $leave->delete();
        return redirect()->route('approver.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }

    public function export(Request $request)
    {
        try {
            // (opsional) disable debugbar yang suka nyisipin output
            if (app()->bound('debugbar')) {
                app('debugbar')->disable();
            }

            // bersihkan buffer agar XLSX tidak ketimpa
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $filters = [
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
            ];

            $filename = 'leave-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new LeavesExport($filters), $filename);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Export error: ' . $e->getMessage());

            // Return JSON error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Employee.leaves.pdf', compact('leave'));
        return $pdf->download('leave-details.pdf');
    }

    public function updateSelf(Request $request, Leave $leave)
    {
        // Check if the user has permission to update this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow updating if the leave is still pending
        if ($leave->status_1 !== 'pending') {
            return redirect()->back()
                ->with('error', 'You cannot update a leave request that has already been processed.');
        }

        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        ]);

        $leave->date_start = $request->date_start;
        $leave->date_end = $request->date_end;
        $leave->reason = $request->reason;
        $leave->status_1 = 'pending';
        $leave->note_1 = NULL;
        $leave->save();

        // Send notification email to the approver
        $manager = User::where('role', Roles::Manager->value)->first();
        if ($manager) {
            $token = Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($leave),   // App\Models\Leave
                'model_id' => $leave->id,
                'approver_user_id' => $manager->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            Mail::to($manager->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $manager->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email
                )
            );
        }

        return redirect()->route('approver.leaves.index')
            ->with('success', 'Leave request updated successfully.');
    }

}
