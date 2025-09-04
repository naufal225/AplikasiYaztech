<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Roles;
use App\TypeRequest;
use App\Models\Overtime;
use App\Models\User;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ApprovalLink;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Str;
use Exception;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // --- Query untuk "Your Overtimes"
        $yourOvertimesQuery = Overtime::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $yourOvertimesQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourOvertimesQuery->where('date_end', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $status = $request->status;

            $yourOvertimesQuery->where(function ($q) use ($status) {
                if ($status === 'rejected') {
                    $q->where('status_1', 'rejected')
                        ->orWhere('status_2', 'rejected');
                } elseif ($status === 'approved') {
                    $q->where('status_1', 'approved')
                        ->where('status_2', 'approved');
                } elseif ($status === 'pending') {
                    $q->where(function ($sub) {
                        $sub->where('status_1', 'pending')
                            ->orWhere('status_2', 'pending');
                    })
                    ->where('status_1', '!=', 'rejected')
                    ->where('status_2', '!=', 'rejected')
                    ->where(function ($sub) {
                        $sub->where('status_1', '!=', 'approved')
                            ->orWhere('status_2', '!=', 'approved');
                    });
                }
            });
        }

        $yourOvertimes = $yourOvertimesQuery->paginate(5, ['*'], 'your_page');


        // --- Query untuk "All Overtimes - Marked Down"
        $allOvertimesDoneQuery = Overtime::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->where('status_2', 'approved')
            ->where('marked_down', true)
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allOvertimesDoneQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allOvertimesDoneQuery->where('date_end', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allOvertimesDone = $allOvertimesDoneQuery->paginate(5, ['*'], 'all_page_done');


        // --- Query untuk "All Overtimes" (pakai lock)
        $allOvertimes = collect();
        DB::transaction(function () use (&$allOvertimes, $request) {
            $query = Overtime::with(['employee', 'approver'])
                ->where('status_1', 'approved')
                ->where('status_2', 'approved')
                ->where('marked_down', false)
                ->where(function ($q) {
                    $q->whereNull('locked_by')
                    ->orWhere(function ($q2) {
                        $q2->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) < ?', [now()]);
                    })
                    ->orWhere(function ($q3) {
                        $q3->where('locked_by', Auth::id())
                            ->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) >= ?', [now()]);
                    });
                })
                ->orderBy('created_at', 'asc');

            if ($request->filled('from_date')) {
                $query->where('date_start', '>=',
                    Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
                );
            }

            if ($request->filled('to_date')) {
                $query->where('date_end', '<=',
                    Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
                );
            }

            // Ambil data & lock untuk user ini
            $allOvertimes = $query->limit(5)->lockForUpdate()->get();

            if ($allOvertimes->isNotEmpty()) {
                Overtime::whereIn('id', $allOvertimes->pluck('id'))
                    ->update([
                        'locked_by' => Auth::id(),
                        'locked_at' => now(),
                    ]);
            }
        });


        // --- Hitung statistik
        $dataAll = Overtime::query();
        $countsAll = $dataAll->where('status_1', 'approved')
            ->where('status_2', 'approved');

        $totalRequests = $dataAll->count();
        $approvedRequests = (int) $countsAll->withFinalStatusCount()->first()->approved;
        $markedRequests = (int) $countsAll->where('marked_down', true)->count();
        $totalAllNoMark = (int) $countsAll->where('marked_down', false)->count();

        $countsYours = (clone $yourOvertimesQuery)->withFinalStatusCount()->first();
        $totalYoursRequests = (int) $yourOvertimesQuery->count();
        $pendingYoursRequests = (int) $countsYours->pending;
        $approvedYoursRequests = (int) $countsYours->approved;
        $rejectedYoursRequests = (int) $countsYours->rejected;


        // --- Manager
        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.overtimes.overtime-show', compact(
            'yourOvertimes',
            'allOvertimes',
            'allOvertimesDone',
            'totalRequests',
            'approvedRequests',
            'markedRequests',
            'totalAllNoMark',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests',
            'manager'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Finance.overtimes.overtime-request', compact('approvers'));
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

        $hours = floor($overtimeMinutes / 60);
        $minutes = $overtimeMinutes % 60;

        DB::transaction(function () use ($start, $end, $overtimeMinutes, $hours, $minutes, $request) {
            $overtime = new Overtime();
            $overtime->employee_id = Auth::id();
            $overtime->customer = $request->customer;
            $overtime->date_start = $start;
            $overtime->date_end = $end;
            $overtime->total = (int) ($hours * (int) env('OVERTIME_COSTS', 0)) + (int) env('MEAL_COSTS', 0);

            // Cek apakah user adalah leader division
            $isLeader = \App\Models\Division::where('leader_id', Auth::id())->exists();

            if ($isLeader) {
                $overtime->status_1 = 'approved';
                $overtime->status_2 = 'pending';
            } else {
                $overtime->status_1 = 'pending';
                $overtime->status_2 = 'pending';
            }

            $overtime->save();

            $token = null;

            if ($isLeader) {
                // --- Jika leader, langsung kirim ke Manager (level 2)
                $manager = User::where('role', Roles::Manager->value)->first();

                if ($manager) {
                    $token = \Illuminate\Support\Str::random(48);
                    ApprovalLink::create([
                        'model_type' => get_class($overtime),   // App\Models\overtime
                        'model_id' => $overtime->id,
                        'approver_user_id' => $manager->id,
                        'level' => 2, // level 2 berarti arahnya ke manager
                        'scope' => 'both',             // boleh approve & reject
                        'token' => hash('sha256', $token), // simpan hash, kirim raw
                        'expires_at' => now()->addDays(3),  // masa berlaku
                    ]);
                }

                DB::afterCommit(function () use ($overtime, $token) {
                    $fresh = $overtime->fresh();
                    event(new \App\Events\OvertimeLevelAdvanced(
                        $fresh,
                        Auth::user()->division_id,
                        'manager'
                    ));

                    if (!$fresh || !$token) {
                        return;
                    }

                    $linkTanggapan = route('public.approval.show', $token);

                    $manager = User::where('role', Roles::Manager->value)->first();
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: Auth::user()->name,
                            namaApprover: $manager->name,
                            linkTanggapan: $linkTanggapan,
                            emailPengaju: Auth::user()->email,
                        )
                    );
                });

            } else {
                // --- Kalau bukan leader, jalur normal ke approver (team lead)
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

                DB::afterCommit(function () use ($overtime, $request, $token) {
                    $fresh = $overtime->fresh(); // ambil ulang (punya created_at dll)
                    // dd("jalan");
                    event(new \App\Events\ReimbursementSubmitted($fresh, Auth::user()->division_id));

                    // Kalau tidak ada approver atau token, jangan kirim email
                    if (!$fresh || !$fresh->approver || !$token) {
                        return;
                    }

                    $linkTanggapan = route('public.approval.show', $token);

                    Mail::to($overtime->approver->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: Auth::user()->name,
                            namaApprover: $overtime->approver->name,
                            linkTanggapan: $linkTanggapan,
                            emailPengaju: Auth::user()->email,
                        )
                    );
                });
            }
            
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

        return redirect()->route('finance.overtimes.index')
            ->with('success', 'Overtime submitted. Total: ' . $hours . ' hours ' . $minutes . ' minutes');
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        $overtime->load(['employee', 'approver']);
        return view('Finance.overtimes.overtime-detail', compact('overtime'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Overtime $overtime)
    {
        $pdf = Pdf::loadView('Finance.overtimes.pdf', compact('overtime'));
        return $pdf->download('overtime-details-finance.pdf');
    }
}
