<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
