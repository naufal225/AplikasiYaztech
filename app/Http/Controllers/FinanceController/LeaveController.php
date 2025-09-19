<?php

namespace App\Http\Controllers\FinanceController;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Models\Leave;
use App\Models\User;
use App\Services\LeaveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService)
    {
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $tahunSekarang = now()->year;

        $yourLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('employee_id', $userId)
            ->orderByDesc('created_at');

        if ($request->filled('from_date')) {
            $yourLeavesQuery->where(
                'date_start',
                '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourLeavesQuery->where(
                'date_end',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $yourLeavesQuery->where(function ($query) use ($status) {
                if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                    $query->where('status_1', $status);
                }
            });
        }

        $yourLeaves = $yourLeavesQuery
            ->paginate(10, ['*'], 'your_page')
            ->withQueryString();

        $allLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->orderByDesc('created_at');

        if ($request->filled('from_date')) {
            $allLeavesQuery->where(
                'date_start',
                '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allLeavesQuery->where(
                'date_start',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allLeaves = $allLeavesQuery
            ->paginate(10, ['*'], 'all_page')
            ->withQueryString();

        $counts = (clone $allLeavesQuery)->withFinalStatusCount()->first();
        $totalRequests = Leave::count();
        $approvedRequests = (int) ($counts->approved ?? 0);

        $countsYours = (clone $yourLeavesQuery)->withFinalStatusCount()->first();
        $totalYoursRequests = (int) ($countsYours->total ?? 0);
        $pendingYoursRequests = (int) ($countsYours->pending ?? 0);
        $approvedYoursRequests = (int) ($countsYours->approved ?? 0);
        $rejectedYoursRequests = (int) ($countsYours->rejected ?? 0);

<<<<<<< HEAD
        $totalHariCuti = Leave::where('employee_id', $userId)
=======
        // Hitung total cuti
        $tahunSekarang = now()->year;

        // Ambil hari libur dari tabel holidays
        $hariLibur = \App\Models\Holiday::whereYear('holiday_date', $tahunSekarang)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $totalHariCuti = Leave::where('employee_id', Auth::id())
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
            ->where('status_1', 'approved')
            ->where(function ($query) use ($tahunSekarang) {
                $query->whereYear('date_start', $tahunSekarang)
                    ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang, $hariLibur) {
                $start = Carbon::parse($cuti->date_start);
                $end   = Carbon::parse($cuti->date_end);

<<<<<<< HEAD
                if ($start->year < $tahunSekarang) {
                    $start = Carbon::create($tahunSekarang, 1, 1);
                }

                if ($end->year > $tahunSekarang) {
                    $end = Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
=======
                return $this->hitungHariCuti($start, $end, $tahunSekarang, $hariLibur);
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
            });

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - $totalHariCuti;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.leaves.leave-show', compact(
            'yourLeaves',
            'allLeaves',
            'totalRequests',
            'approvedRequests',
            'manager',
            'sisaCuti',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests'
        ));
    }

    public function create()
    {
<<<<<<< HEAD
        $sisaCuti = $this->leaveService->sisaCuti(Auth::user());
=======
        $tahunSekarang = now()->year;

        // Ambil daftar hari libur dalam tahun ini
        $hariLibur = \App\Models\Holiday::whereYear('holiday_date', $tahunSekarang)
            ->pluck('holiday_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $holidays = \App\Models\Holiday::pluck('holiday_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();


        // Hitung total cuti yang sudah diambil
        $totalHariCuti = (int) Leave::where('employee_id', Auth::id())
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang, $hariLibur) {
                $start = \Carbon\Carbon::parse($cuti->date_start);
                $end   = \Carbon\Carbon::parse($cuti->date_end);

                // Batasi tanggal ke dalam tahun berjalan
                if ($start->year < $tahunSekarang) {
                    $start = \Carbon\Carbon::create($tahunSekarang, 1, 1);
                }
                if ($end->year > $tahunSekarang) {
                    $end = \Carbon\Carbon::create($tahunSekarang, 12, 31);
                }

                $hariCuti = 0;

                while ($start->lte($end)) {
                    // Skip kalau Sabtu/Minggu
                    if ($start->isWeekend()) {
                        $start->addDay();
                        continue;
                    }

                    // Skip kalau hari libur
                    if (in_array($start->format('Y-m-d'), $hariLibur)) {
                        $start->addDay();
                        continue;
                    }

                    $hariCuti++;
                    $start->addDay();
                }

                return $hariCuti;
            });

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - $totalHariCuti;
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b

        if ($sisaCuti <= 0) {
            abort(422, 'Sisa cuti tidak cukup.');
        }

<<<<<<< HEAD
        return view('Finance.leaves.leave-request', compact('sisaCuti'));
=======
        return view('Finance.leaves.leave-request', compact('sisaCuti', 'holidays'));
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
    }

    public function store(StoreLeaveRequest $request)
    {
        try {
            $this->leaveService->store($request->validated());

<<<<<<< HEAD
            return redirect()
                ->route('finance.leaves.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

=======
        $tahunSekarang = now()->year;

        // Ambil semua hari libur di tahun ini
        $hariLibur = \App\Models\Holiday::whereYear('holiday_date', $tahunSekarang)
            ->pluck('holiday_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Hitung cuti yang sudah terpakai
        $totalHariCuti = (int) Leave::where('employee_id', Auth::id())
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang, $hariLibur) {
                $start = \Carbon\Carbon::parse($cuti->date_start);
                $end   = \Carbon\Carbon::parse($cuti->date_end);
                return $this->hitungHariCuti($start, $end, $tahunSekarang, $hariLibur);
            });

        // Hitung cuti yang sedang diajukan
        $startBaru = \Carbon\Carbon::parse($request->date_start);
        $endBaru   = \Carbon\Carbon::parse($request->date_end);
        $hariCutiBaru = $this->hitungHariCuti($startBaru, $endBaru, $tahunSekarang, $hariLibur);

        $jatahTahunan = (int) env('CUTI_TAHUNAN', 20);
        $sisaCuti = $jatahTahunan - $totalHariCuti;

        if ($hariCutiBaru > $sisaCuti) {
            return back()->with('error', "Sisa cuti hanya {$sisaCuti} hari, tidak bisa ajukan {$hariCutiBaru} hari.");
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
            DB::afterCommit(function () use ($leave, $request, $tokenRaw, $manager) {
                $fresh = $leave->fresh(); // ambil ulang (punya created_at dll)

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

        return redirect()->route('finance.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Count leave days excluding weekends and holidays.
     */
    public function hitungHariCuti($start, $end, $tahunSekarang, $hariLibur)
    {
        // clone supaya tidak merusak object asli
        $start = $start->copy();
        $end   = $end->copy();

        if ($start->year < $tahunSekarang) {
            $start = \Carbon\Carbon::create($tahunSekarang, 1, 1);
        }
        if ($end->year > $tahunSekarang) {
            $end = \Carbon\Carbon::create($tahunSekarang, 12, 31);
        }

        $hariCuti = 0;
        while ($start->lte($end)) {
            if ($start->isWeekend()) {
                $start->addDay();
                continue;
            }

            if (in_array($start->format('Y-m-d'), $hariLibur)) {
                $start->addDay();
                continue;
            }

            $hariCuti++;
            $start->addDay();
        }

        return $hariCuti;
    }

    /**
     * Display the specified resource.
     */
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
    public function show(Leave $leave)
    {
        $leave->load(['employee', 'approver']);

        return view('Finance.leaves.leave-detail', compact('leave'));
    }

    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Finance.leaves.pdf', compact('leave'));

        return $pdf->download('leave-details-finance.pdf');
    }

    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = Leave::with('employee')
            ->where('status_1', 'approved');

        if ($dateFrom && $dateTo) {
            $query->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereDate('date_start', '<=', $dateTo)
                    ->whereDate('date_end', '>=', $dateFrom);
            });
        }

        $leaves = $query->get();

        if ($leaves->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk filter tersebut.');
        }

        $zipFileName = 'LeaveRequests_' . Carbon::now()->format('YmdHis') . '.zip';
        $zipPath = Storage::disk('public')->path($zipFileName);

        $tempFolder = 'temp_leaves';
        if (!Storage::disk('public')->exists($tempFolder)) {
            Storage::disk('public')->makeDirectory($tempFolder);
        }

        $files = [];

        foreach ($leaves as $leave) {
            $pdf = Pdf::loadView('Finance.leaves.pdf', compact('leave'));
            $fileName = "leave_{$leave->employee->name}_{$leave->id}.pdf";
            $filePath = "{$tempFolder}/{$fileName}";
            Storage::disk('public')->put($filePath, $pdf->output());
            $files[] = Storage::disk('public')->path($filePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        foreach ($files as $file) {
            @unlink($file);
        }
        Storage::disk('public')->deleteDirectory($tempFolder);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function edit(Leave $leave)
    {
        $user = Auth::user();
        if ($user->id !== (int) $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

<<<<<<< HEAD
=======
        $holidays = \App\Models\Holiday::pluck('holiday_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Only allow editing if the leave is still pending
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
        if ($leave->status_1 !== 'pending') {
            return redirect()->route('finance.leaves.show', $leave->id)
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }

        return view('Finance.leaves.leave-edit', compact('leave', 'holidays'));
    }

    public function update(UpdateLeaveRequest $request, Leave $leave)
    {
<<<<<<< HEAD
        try {
            $this->leaveService->update($leave, $request->validated());

            return redirect()
                ->route('finance.leaves.index')
                ->with('success', 'Leave request updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
=======
        $user = Auth::user();
        if ($user->id !== (int) $leave->employee_id) {
            abort(403, 'Unauthorized action.');
>>>>>>> 1a42a6f436ab20e0edcc41816a8f1d352383722b
        }
    }

    public function destroy(Leave $leave)
    {
        $user = Auth::user();
        if ($user->id !== $leave->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if ($leave->status_1 !== 'pending' && $user->role !== Roles::Admin->value) {
            return redirect()->route('finance.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        $leave->delete();

        return redirect()
            ->route('finance.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
