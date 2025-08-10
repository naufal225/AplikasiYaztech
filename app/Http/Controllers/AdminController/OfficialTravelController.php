<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\OfficialTravel;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
