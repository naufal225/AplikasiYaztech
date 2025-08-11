<?php

namespace App\Http\Controllers\AdminController;

use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $query = Overtime::with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        $overtimes = $query->paginate(10);
        $totalRequests = Overtime::count();
        $pendingRequests = Overtime::where('status', 'pending')->count();
        $approvedRequests = Overtime::where('status', 'approved')->count();
        $rejectedRequests = Overtime::where('status', 'rejected')->count();

        return view('admin.overtime.index', compact('overtimes', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    public function show(Overtime $overtime)
    {
        $overtime->load(['employee', 'approver']);
        return view('admin.overtime.show', compact('overtime'));
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
}
