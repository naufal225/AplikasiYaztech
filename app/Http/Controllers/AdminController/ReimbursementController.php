<?php

namespace App\Http\Controllers\AdminController;

use App\Exports\ReimbursementsExport;
use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $query = Reimbursement::with(['approver', 'customer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('date', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $reimbursements = $query->paginate(10);
        $totalRequests = Reimbursement::count();
        $pendingRequests = Reimbursement::where('status', 'pending')->count();
        $approvedRequests = Reimbursement::where('status', 'approved')->count();
        $rejectedRequests = Reimbursement::where('status', 'rejected')->count();

        return view('admin.reimbursement.index', compact('reimbursements', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

     public function show($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);
        $reimbursement->load(['approver', 'customer']);

        return view('admin.reimbursement.show', compact('reimbursement'));
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

            $filename = 'reimbursement-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new ReimbursementsExport($filters), $filename);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Export error: ' . $e->getMessage());

            // Return JSON error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
