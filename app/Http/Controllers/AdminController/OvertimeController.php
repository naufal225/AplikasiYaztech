<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
