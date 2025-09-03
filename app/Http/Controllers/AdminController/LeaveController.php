<?php

namespace App\Http\Controllers\AdminController;

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
        $allUsersQuery = Leave::with(['employee', 'approver'])
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
            $ownRequestsQuery->where('date_start', '>=', $fromDate);
            $allUsersQuery->where('date_start', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta');
            $ownRequestsQuery->where('date_start', '<=', $toDate);
            $allUsersQuery->where('date_start', '<=', $toDate);
        }

        $ownRequests = $ownRequestsQuery->paginate(10, ['*'], 'own_page');
        $allUsersRequests = $allUsersQuery->paginate(10, ['*'], 'all_page');


        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) Leave::where('employee_id', Auth::id())
            ->whereYear('date_start', now()->year)->count();

        $totalRequests = Leave::count();
        $pendingRequests = Leave::where('status_1', 'pending')->count();
        $approvedRequests = Leave::where('status_1', 'approved')->count();
        $rejectedRequests = Leave::where('status_1', 'rejected')->count();

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('admin.leave-request.index', compact(
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
        $leave->load(['employee', 'approver']);
        return view('admin.leave-request.show', compact('leave'));
    }

    public function create()
    {
        return view('admin.leave-request.create');
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

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function edit(Leave $leave)
    {
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }


        // Only allow editing if the leave is still pending
        if ($leave->status_1 !== 'pending') {
            return redirect()->back()
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }

        return view('admin.leave-request.update', compact('leave'));
    }

    public function update(Request $request, Leave $leave)
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

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        // Check if the user has permission to delete this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow deleting if the leave is still pending
        if (($leave->status_1 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        $leave->delete();
        return redirect()->route('admin.leaves.index')
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

}
