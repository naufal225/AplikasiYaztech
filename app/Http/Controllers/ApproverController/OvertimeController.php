<?php

namespace App\Http\Controllers\ApproverController;

use App\Events\OvertimeLevelAdvanced;
use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\Overtime;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        // Query for user's own requests (all statuses)
        $ownRequestsQuery = Overtime::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = Overtime::with(['employee', 'approver'])->forLeader(Auth::id())
            ->where(function ($q) {
                $q->where('employee_id', '!=', Auth::id())
                    ->orWhere(function ($subQ) {
                        $subQ->where('employee_id', Auth::id())
                            ->where('status_2', 'approved');
                    });
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $statusFilter = function ($query) use ($request) {
                switch ($request->status) {
                    case 'approved':
                        // approved = dua-duanya approved
                        $query->where('status_1', 'approved')
                            ->where('status_2', 'approved');
                        break;

                    case 'rejected':
                        // rejected = salah satu rejected
                        $query->where(function ($q) {
                            $q->where('status_1', 'rejected')
                                ->orWhere('status_2', 'rejected');
                        });
                        break;

                    case 'pending':
                        // pending = tidak ada rejected DAN (minimal salah satu pending)
                        $query->where(function ($q) {
                            $q->where(function ($qq) {
                                $qq->where('status_1', 'pending')
                                    ->orWhere('status_2', 'pending');
                            })->where(function ($qq) {
                                $qq->where('status_1', '!=', 'rejected')
                                    ->where('status_2', '!=', 'rejected');
                            });
                        });
                        break;

                    default:
                        // nilai status tak dikenal: biarkan tanpa filter atau lempar 422
                        // optional: $query->whereRaw('1=0');
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

        $totalRequests = Overtime::count();
        $pendingRequests = Overtime::where('status_1', 'pending')
            ->orWhere('status_2', 'pending')->count();
        $approvedRequests = Overtime::where('status_1', 'approved')
            ->where('status_2', 'approved')->count();
        $rejectedRequests = Overtime::where('status_1', 'rejected')
            ->orWhere('status_2', 'rejected')->count();

        Overtime::whereNull('seen_by_approver_at')
            ->whereHas('employee', fn($q) => $q->where('division_id', auth()->user()->division_id))
            ->update(['seen_by_approver_at' => now()]);
        $manager = User::where('role', Roles::Manager->value)->first();


        return view('approver.overtime.index', compact('allUsersRequests', 'ownRequests', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }


    public function show($id)
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->load(['employee', 'approver']);
        return view('approver.overtime.show', compact('overtime'));
    }

    public function edit(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
            return redirect()->route('approver.overtimes.show', $overtime->id)
                ->with('error', 'You cannot edit an overtime request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('approver.overtime.update', compact('overtime', 'approvers'));
    }

    public function updateSelf(Request $request, Overtime $overtime)
    {
        $user = Auth::user();

        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
            return redirect()->route('approver.overtimes.show', $overtime->id)
                ->with('error', 'You cannot update an overtime request that has already been processed.');
        }

        $request->validate([
            'customer' => 'required',
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
        ]);

        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        $overtimeMinutes = $start->diffInMinutes($end);

        $overtimeHours = round($overtimeMinutes / 60);

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        // Simpan data
        $overtime->customer = $request->customer;
        $overtime->date_start = $request->date_start;
        $overtime->date_end = $request->date_end;
        $overtime->total = (int) ($overtimeHours * (int) env('OVERTIME_COSTS', 0)) + (int) env('MEAL_COSTS', 0);
        $overtime->status_1 = 'pending';
        $overtime->status_2 = 'pending';
        $overtime->note_1 = NULL;
        $overtime->note_2 = NULL;
        $overtime->save();

        // Send notification email to the approver
        if ($overtime->approver) {
            $token = \Illuminate\Support\Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($overtime),   // App\Models\overtime
                'model_id' => $overtime->id,
                'approver_user_id' => $overtime->approver->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            $hours = floor($overtimeMinutes / 60);
            $minutes = $overtimeMinutes % 60;

            Mail::to($overtime->approver->email)->queue(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $overtime->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                )
            );
        }

        return redirect()->route('approver.overtimes.show', $overtime->id)
            ->with('success', 'Overtime request updated successfully. Total overtime: ' . $hours . ' hours ' . $minutes . ' minutes');
    }

    public function create()
    {
        return view('approver.overtime.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required',
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ], [
            'customer.required' => 'Customer harus diisi.',
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
        ]);

        // Parsing waktu input
        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        // Hitung langsung dari date_start
        $overtimeMinutes = $start->diffInMinutes($end);
        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        $hours = round($overtimeMinutes / 60);
        $minutes = $overtimeMinutes % 60;

        DB::transaction(function () use ($start, $end, $overtimeMinutes, $hours, $minutes, $request) {
            $overtime = new Overtime();
            $overtime->employee_id = Auth::id();
            $overtime->customer = $request->customer;
            $overtime->date_start = $start;
            $overtime->date_end = $end;
            $overtime->total = (int) ((int) ($hours * (int) env('OVERTIME_COSTS', 0)) + (int) env('MEAL_COSTS', 0));
            $overtime->status_1 = 'pending';
            $overtime->status_2 = 'pending';
            $overtime->save();

            $token = null;
            // Send notification email to the approver
            if ($overtime->approver) {
                $token = \Illuminate\Support\Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($overtime),   // App\Models\overtime
                    'model_id' => $overtime->id,
                    'approver_user_id' => $overtime->approver->id,
                    'level' => 1, // level 1 berarti arahnya ke team lead
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $token), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);

            }

            DB::afterCommit(function () use ($overtime, $token, $start, $end, $hours, $minutes) {
                $fresh = $overtime->fresh(); // ambil ulang (punya created_at dll)
                // dd("jalan");
                event(new \App\Events\OvertimeSubmitted($fresh, Auth::user()->division_id));

                // Kalau tidak ada approver atau token, jangan kirim email

                if (!$fresh || !$fresh->approver || !$token) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $token);

                Mail::to($overtime->approver->email)->send(
                    new \App\Mail\SendMessage(
                        namaPengaju: Auth::user()->name,
                        namaApprover: $overtime->approver->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: Auth::user()->email,
                    )
                );
            });

        });

        return redirect()->route('approver.overtimes.index')
            ->with('success', 'Overtime submitted. Total: ' . $hours . ' hours ' . $minutes . ' minutes');
    }



    public function update(Request $request, Overtime $overtime)
    {
        $validated = $request->validate([
            'status_1' => 'string|in:approved,rejected',
            'status_2' => 'string|in:approved,rejected',
            'note_1' => 'nullable|string',
            'note_2' => 'nullable|string',
        ], [
            'status_1.string' => 'Status must be a valid string.',
            'status_1.in' => 'Status must approved or rejected.',

            'status_2.string' => 'Status must be a valid string.',
            'status_2.in' => 'Status must approved or rejected.',

            'note_1.string' => 'Note must be a valid string.',
            'note_2.string' => 'Note must be a valid string.',
        ]);

        $status = '';

        if ($request->has('status_1')) {
            $overtime->update([
                'status_1' => $validated['status_1'],
                'note_1' => $validated['note_1'] ?? null
            ]);
            $status = $validated['status_1'];
        } else if ($request->has('status_2')) {
            $overtime->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? null
            ]);
            $status = $validated['status_2'];
        }

        return redirect()->route('approver.overtimes.index')->with('success', 'Overtime request ' . $status . ' successfully.');
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

            $filename = 'overtime-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new OvertimesExport($filters), $filename);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Export error: ' . $e->getMessage());

            // Return JSON error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }



    public function destroy(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if (($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('approver.overtimes.show', $overtime->id)
                ->with('error', 'You cannot delete an overtime request that has already been processed.');
        }

        $overtime->delete();

        return redirect()->route('approver.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
    }
}
