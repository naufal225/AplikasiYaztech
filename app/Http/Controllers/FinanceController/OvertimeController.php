<?php

namespace App\Http\Controllers\FinanceController;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOvertimeRequest;
use App\Http\Requests\UpdateOvertimeRequest;
use App\Models\Overtime;
use App\Models\User;
use App\Services\OvertimeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeService $overtimeService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // --- Query untuk "Your Overtimes"
        $yourOvertimesQuery = Overtime::with(['employee', 'approver'])
            ->where('employee_id', $userId)
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

        $yourOvertimes = $yourOvertimesQuery->paginate(5, ['*'], 'your_page')->withQueryString();

        // --- Query untuk "All Overtimes Done (Marked Down)"
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

        $allOvertimesDone = $allOvertimesDoneQuery->paginate(5, ['*'], 'all_page_done')->withQueryString();

        // --- Query untuk "All Overtimes Not Marked (lockable)"
        $allOvertimes = collect();
        DB::transaction(function () use (&$allOvertimes, $request, $userId) {
            $query = Overtime::with(['employee', 'approver'])
                ->where('status_1', 'approved')
                ->where('status_2', 'approved')
                ->where('marked_down', false)
                ->where(function ($q) use ($userId) {
                    $q->whereNull('locked_by')
                    ->orWhere(function ($q2) {
                        $q2->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) < ?', [now()]);
                    })
                    ->orWhere(function ($q3) use ($userId) {
                        $q3->where('locked_by', $userId)
                            ->whereRaw('DATE_ADD(locked_at, INTERVAL 60 MINUTE) >= ?', [now()]);
                    });
                })
                ->orderBy('created_at', 'asc');

            if (request()->filled('from_date')) {
                $query->where('date_start', '>=',
                    Carbon::parse(request()->from_date)->startOfDay()->timezone('Asia/Jakarta')
                );
            }

            if (request()->filled('to_date')) {
                $query->where('date_end', '<=',
                    Carbon::parse(request()->to_date)->endOfDay()->timezone('Asia/Jakarta')
                );
            }

            $allOvertimes = $query->limit(5)->lockForUpdate()->get();

            if ($allOvertimes->isNotEmpty()) {
                Overtime::whereIn('id', $allOvertimes->pluck('id'))
                    ->update([
                        'locked_by' => $userId,
                        'locked_at' => now(),
                    ]);
            }
        });

        // --- Statistik
        $dataAll = Overtime::where('status_1', 'approved')
            ->where('status_2', 'approved');

        $totalRequests = $dataAll->count();
        $approvedRequests = optional($dataAll->withFinalStatusCount()->first())->approved ?? 0;
        $markedRequests = (clone $dataAll)->where('marked_down', true)->count();
        $totalAllNoMark = (clone $dataAll)->where('marked_down', false)->count();

        $countsYours = optional((clone $yourOvertimesQuery)->withFinalStatusCount()->first());
        $totalYoursRequests = $yourOvertimesQuery->count();
        $pendingYoursRequests = $countsYours->pending ?? 0;
        $approvedYoursRequests = $countsYours->approved ?? 0;
        $rejectedYoursRequests = $countsYours->rejected ?? 0;

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
    public function store(StoreOvertimeRequest $request)
    {
        try {
            $this->overtimeService->store($request->validated());

            return redirect()->route('finance.overtimes.index')
                ->with('success', 'Overtime request submitted successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $isLeader = \App\Models\Division::where('leader_id', $user->id)->exists();

        if (($isLeader && $overtime->status_2 !== 'pending') || (!$isLeader && $overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending')) {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot edit an overtime request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();

        return view('Finance.overtimes.overtime-edit', compact('overtime', 'approvers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOvertimeRequest $request, Overtime $overtime)
    {
        $user = Auth::user();

        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->overtimeService->update($overtime, $request->validated());

            return redirect()->route('finance.overtimes.index')
                ->with('success', 'Overtime request updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Mark selected overtimes as done (marked_down = true).
     */

    public function markedDone(Request $request)
    {
        $ids = $request->input('ids', []);

        try {
            DB::transaction(function () use ($ids) {
                $records = Overtime::whereIn('id', $ids)
                    ->where('marked_down', false)
                    ->where('locked_by', Auth::id())
                    ->lockForUpdate()
                    ->get();

                if ($records->isEmpty()) {
                    throw new Exception('No overtimes available to mark as done.');
                }

                foreach ($records as $rec) {
                    $rec->update([
                        'marked_down' => true,
                        'locked_by'   => null,
                        'locked_at'   => null,
                    ]);
                }
            });

            return redirect()
                ->route('finance.overtimes.index')
                ->with('success', 'Selected overtimes marked as done.');
        } catch (Exception $e) {
            return redirect()
                ->route('finance.overtimes.index')
                ->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk export approved requests as PDFs in a ZIP file.
     */
    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = Overtime::with('employee')->where('status_1', 'approved')->where('status_2', 'approved')->where('marked_down', true);

        if ($dateFrom && $dateTo) {
            $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('date_start', '<=', $dateTo)
                ->whereDate('date_end', '>=', $dateFrom);
            });
        }

        $overtimes = $query->get();

        if ($overtimes->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk filter tersebut.');
        }

        $zipFileName = 'OvertimeRequests_' . Carbon::now()->format('YmdHis') . '.zip';
        $zipPath = Storage::disk('public')->path($zipFileName);

        // Folder sementara untuk menyimpan PDF
        $tempFolder = 'temp_overtimes';
        if (!Storage::disk('public')->exists($tempFolder)) {
            Storage::disk('public')->makeDirectory($tempFolder);
        }

        $files = [];

        foreach ($overtimes as $overtime) {
            $pdf = Pdf::loadView('Finance.overtimes.pdf', compact('overtime'));
            $fileName = "overtime_{$overtime->employee->name}_" . $overtime->id . ".pdf";
            $filePath = "{$tempFolder}/{$fileName}";
            Storage::disk('public')->put($filePath, $pdf->output());
            $files[] = Storage::disk('public')->path($filePath);
        }

        // Buat ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Bersihkan file sementara
        foreach ($files as $file) {
            @unlink($file);
        }
        Storage::disk('public')->deleteDirectory($tempFolder);

        // Return download
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $isLeader = \App\Models\Division::where('leader_id', $user->id)->exists();

        if (($isLeader && $overtime->status_2 !== 'pending') || (!$isLeader && $overtime->status_1 !== 'pending')) {
            return redirect()->route('finance.overtimes.show', $overtime->id)
                ->with('error', 'You cannot delete an overtime request that has already been processed.');
        }

        if (\App\Models\ApprovalLink::where('model_id', $overtime->id)->where('model_type', get_class($overtime))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $overtime->id)->where('model_type', get_class($overtime))->delete();
        }

        $overtime->delete();

        return redirect()->route('finance.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
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
