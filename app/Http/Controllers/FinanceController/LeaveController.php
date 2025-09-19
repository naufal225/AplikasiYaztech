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

        $totalHariCuti = Leave::where('employee_id', $userId)
            ->where('status_1', 'approved')
            ->where(function ($query) use ($tahunSekarang) {
                $query->whereYear('date_start', $tahunSekarang)
                    ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang) {
                $start = Carbon::parse($cuti->date_start);
                $end = Carbon::parse($cuti->date_end);

                if ($start->year < $tahunSekarang) {
                    $start = Carbon::create($tahunSekarang, 1, 1);
                }

                if ($end->year > $tahunSekarang) {
                    $end = Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
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
        $sisaCuti = $this->leaveService->sisaCuti(Auth::user());

        if ($sisaCuti <= 0) {
            abort(422, 'Sisa cuti tidak cukup.');
        }

        return view('Finance.leaves.leave-request', compact('sisaCuti'));
    }

    public function store(StoreLeaveRequest $request)
    {
        try {
            $this->leaveService->store($request->validated());

            return redirect()
                ->route('finance.leaves.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

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
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($leave->status_1 !== 'pending') {
            return redirect()->route('finance.leaves.show', $leave->id)
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }

        return view('Finance.leaves.leave-edit', compact('leave'));
    }

    public function update(UpdateLeaveRequest $request, Leave $leave)
    {
        try {
            $this->leaveService->update($leave, $request->validated());

            return redirect()
                ->route('finance.leaves.index')
                ->with('success', 'Leave request updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
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
