<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Roles;
use App\TypeRequest;
use App\Models\Leave;
use App\Models\User;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // --- Query untuk "Your Reimbursements"
        $yourReimbursementsQuery = Reimbursement::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
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

        $yourReimbursements = $yourReimbursementsQuery->paginate(10, ['*'], 'your_page');


        // --- Query untuk "All Reimbursements"
        $allReimbursementsQuery = Reimbursement::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->where('status_2', 'approved')
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allReimbursementsQuery->where('date', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allReimbursementsQuery->where('date', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allReimbursements = $allReimbursementsQuery->paginate(10, ['*'], 'all_page');


        // --- Hitung statistik
        $countsAll = (clone $allReimbursementsQuery)->withFinalStatusCount()->first();
        $totalRequests = Reimbursement::count();
        $approvedRequests = (int) $countsAll->approved;
        $markedRequests = (clone $allReimbursementsQuery)->where('marked_down', '!=', 0)->count();

        $countsYours = (clone $yourReimbursementsQuery)->withFinalStatusCount()->first();
        $totalYoursRequests = (int) $yourReimbursementsQuery->count();
        $pendingYoursRequests = (int) $countsYours->pending;
        $approvedYoursRequests = (int) $countsYours->approved;
        $rejectedYoursRequests = (int) $countsYours->rejected;

        // --- Manager
        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.reimbursements.reimbursement-show', compact(
            'yourReimbursements',
            'allReimbursements',
            'totalRequests',
            'approvedRequests',
            'markedRequests',
            'manager',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests'
        ));
    }

    // public function index(Request $request)
    // {
    //     $queryReal = Reimbursement::whereHas('employee', function ($q) {
    //             $q->where('role', Roles::Employee->value);
    //         });

    //     $query = (clone $queryReal)
    //         ->where('status_1', 'approved')
    //         ->where('status_2', 'approved')
    //         ->with(['employee', 'approver'])
    //         ->orderBy('created_at', 'desc');

    //     $queryClone = (clone $query);

    //     if ($request->filled('from_date')) {
    //         $query->where('date_start', '>=',
    //             Carbon::parse($request->from_date)
    //                 ->startOfDay()
    //                 ->timezone('Asia/Jakarta')
    //         );
    //     }

    //     if ($request->filled('to_date')) {
    //         $query->where('date_start', '<=',
    //             Carbon::parse($request->to_date)
    //                 ->endOfDay()
    //                 ->timezone('Asia/Jakarta')
    //         );
    //     }

    //     $reimbursements = $query->paginate(10);
    //     $counts = $queryClone->withFinalStatusCount()->first();

    //     $totalRequests = (int) $queryClone->count();
    //     $approvedRequests = (int) $counts->approved;

    //     $manager = User::where('role', Roles::Manager->value)->first();

    //     return view('Finance.reimbursements.reimbursement-show', compact('reimbursements', 'totalRequests', 'approvedRequests', 'manager'));
    // }

    /**
     * Display the specified resource.
     */
    public function show(Reimbursement $reimbursement)
    {
        $reimbursement->load(['approver', 'customer']);
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

    /**
     * Bulk export approved requests as PDFs in a ZIP file.
     */
    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = Reimbursement::with('employee')->where('status_1', 'approved')->where('status_2', 'approved');

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
}
