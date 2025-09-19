<?php

namespace App\Http\Controllers\FinanceController;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReimbursementRequest;
use App\Http\Requests\UpdateReimbursementRequest;
use App\Models\Reimbursement;
use App\Models\User;
use App\Services\ReimbursementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ReimbursementController extends Controller
{
    public function __construct(private ReimbursementService $reimbursementService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // --- Query untuk "Your Reimbursements"
        $yourReimbursementsQuery = Reimbursement::with(['employee', 'approver' , 'type'])
            ->where('employee_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $yourReimbursementsQuery->where('date', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourReimbursementsQuery->where('date', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $yourReimbursementsQuery->where(function ($q) use ($status) {
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

        $yourReimbursements = $yourReimbursementsQuery->paginate(5, ['*'], 'your_page')->withQueryString();

        // --- Query untuk "All Reimbursements Done (Marked Down)"
        $allReimbursementsDoneQuery = Reimbursement::with(['employee', 'approver', 'type'])
            ->where('status_1', 'approved')
            ->where('status_2', 'approved')
            ->where('marked_down', true)
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allReimbursementsDoneQuery->where('date', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allReimbursementsDoneQuery->where('date', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allReimbursementsDone = $allReimbursementsDoneQuery->paginate(5, ['*'], 'all_page_done')->withQueryString();

        // --- Query untuk "All Reimbursements Not Marked (lockable)"
        $allReimbursements = collect();
        DB::transaction(function () use (&$allReimbursements, $request, $userId) {
            $query = Reimbursement::with(['employee', 'approver', 'type'])
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
                $query->where('date', '>=',
                    Carbon::parse(request()->from_date)->startOfDay()->timezone('Asia/Jakarta')
                );
            }

            if (request()->filled('to_date')) {
                $query->where('date', '<=',
                    Carbon::parse(request()->to_date)->endOfDay()->timezone('Asia/Jakarta')
                );
            }

            $allReimbursements = $query->limit(5)->lockForUpdate()->get();

            if ($allReimbursements->isNotEmpty()) {
                Reimbursement::whereIn('id', $allReimbursements->pluck('id'))
                    ->update([
                        'locked_by' => $userId,
                        'locked_at' => now(),
                    ]);
            }
        });

        // --- Statistik
        $dataAll = Reimbursement::where('status_1', 'approved')
            ->where('status_2', 'approved');

        $totalRequests = $dataAll->count();
        $approvedRequests = optional($dataAll->withFinalStatusCount()->first())->approved ?? 0;
        $markedRequests = (clone $dataAll)->where('marked_down', true)->count();
        $totalAllNoMark = (clone $dataAll)->where('marked_down', false)->count();

        $countsYours = optional((clone $yourReimbursementsQuery)->withFinalStatusCount()->first());
        $totalYoursRequests = $yourReimbursementsQuery->count();
        $pendingYoursRequests = $countsYours->pending ?? 0;
        $approvedYoursRequests = $countsYours->approved ?? 0;
        $rejectedYoursRequests = $countsYours->rejected ?? 0;

        // --- Manager
        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.reimbursements.reimbursement-show', compact(
            'yourReimbursements',
            'allReimbursements',
            'allReimbursementsDone',
            'totalRequests',
            'markedRequests',
            'totalAllNoMark',
            'approvedRequests',
            'manager',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = \App\Models\ReimbursementType::all();
        return view('Finance.reimbursements.reimbursement-request', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReimbursementRequest $request)
    {
        try {
            $this->reimbursementService->store($request->validated());

            return redirect()->route('finance.reimbursements.index')
                ->with('success', 'Reimbursement request submitted successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Reimbursement $reimbursement)
    {
        $reimbursement->load('approver');
        return view('Finance.reimbursements.reimbursement-detail', compact('reimbursement'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Reimbursement $reimbursement)
    {
        $pdf = Pdf::loadView('Finance.reimbursements.pdf', compact('reimbursement'));
        return $pdf->download('reimbursement-details-finance.pdf');
    }
    public function edit(Reimbursement $reimbursement)
    {
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($reimbursement->status_1 !== 'pending' || $reimbursement->status_2 !== 'pending') {
            return redirect()->route('finance.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot edit a reimbursement request that has already been processed.');
        }

        $types = \App\Models\ReimbursementType::all();

        return view('Finance.reimbursements.reimbursement-edit', compact('reimbursement', 'types'));
    }

    public function update(UpdateReimbursementRequest $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->reimbursementService->update($reimbursement, $request->validated());

            return redirect()->route('finance.reimbursements.index')
                ->with('success', 'Reimbursement request updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Bulk export approved requests as PDFs in a ZIP file.
     */
    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = Reimbursement::with('employee')->where('status_1', 'approved')->where('status_2', 'approved')->where('marked_down', true);

        if ($dateFrom && $dateTo) {
            $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('date', '<=', $dateTo)
                ->whereDate('date', '>=', $dateFrom);
            });
        }

        $reimbursements = $query->get();

        if ($reimbursements->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk filter tersebut.');
        }

        $zipFileName = 'ReimbursementRequests_' . Carbon::now()->format('YmdHis') . '.zip';
        $zipPath = Storage::disk('public')->path($zipFileName);

        // Folder sementara untuk menyimpan PDF
        $tempFolder = 'temp_reimbursements';
        if (!Storage::disk('public')->exists($tempFolder)) {
            Storage::disk('public')->makeDirectory($tempFolder);
        }

        $files = [];

        foreach ($reimbursements as $reimbursement) {
            $pdf = Pdf::loadView('Finance.reimbursements.pdf', compact('reimbursement'));
            $fileName = "reimbursement_{$reimbursement->employee->name}_" . $reimbursement->id . ".pdf";
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
    public function destroy(Reimbursement $reimbursement)
    {
        // Check if the user has permission to delete this reimbursement
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $isLeader = \App\Models\Division::where('leader_id', $user->id)->exists();

        // Only allow deleting if the reimbursement is still pending
        if (($isLeader && $reimbursement->status_2 !== 'pending') || (!$isLeader && $reimbursement->status_1 !== 'pending')) {
            return redirect()->route('finance.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot delete a reimbursement request that has already been processed.');
        }
        
        if ($reimbursement->invoice_path) {
            Storage::disk('public')->delete($reimbursement->invoice_path);
        }

        if (\App\Models\ApprovalLink::where('model_id', $reimbursement->id)->where('model_type', get_class($reimbursement))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $reimbursement->id)->where('model_type', get_class($reimbursement))->delete();
        }

        $reimbursement->delete();

        return redirect()->route('finance.reimbursements.index')
            ->with('success', 'Reimbursement request deleted successfully.');
    }
}
