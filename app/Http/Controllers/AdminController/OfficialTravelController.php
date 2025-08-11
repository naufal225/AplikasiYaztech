<?php

namespace App\Http\Controllers\AdminController;

use App\Exports\OfficialTravelsExport;
use App\Http\Controllers\Controller;
use App\Models\OfficialTravel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OfficialTravelController extends Controller
{
    public function index(Request $request)
    {
        $query = OfficialTravel::with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $officialTravels = $query->paginate(10);
        $totalRequests = OfficialTravel::count();
        $pendingRequests = OfficialTravel::where('status', 'pending')->count();
        $approvedRequests = OfficialTravel::where('status', 'approved')->count();
        $rejectedRequests = OfficialTravel::where('status', 'rejected')->count();

        return view('admin.official-travel.index', compact('officialTravels', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

     public function show($id)
    {
        $officialTravel = OfficialTravel::findOrFail($id);
        $officialTravel->load(['employee', 'approver']);
        return view('admin.official-travel.show', compact('officialTravel'));
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

            $filename = 'official-travel-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new OfficialTravelsExport($filters), $filename);
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
