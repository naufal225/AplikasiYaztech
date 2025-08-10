<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request) {
        $query = Leave::with(['employee', 'approver'])
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
            $query->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $leaves = $query->paginate(10);
        $totalRequests = Leave::count();
        $pendingRequests = Leave::where('status', 'pending')->count();
        $approvedRequests = Leave::where('status', 'approved')->count();
        $rejectedRequests = Leave::where('status', 'rejected')->count();

        return view('admin.leave-request.index', compact('leaves', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    public function create() {

    }

    public function store(Request $request) {

    }

    public function edit(Leave $leave) {

    }

    public function update(Request $request) {

    }

    public function destroy(Leave $leave)  {

    }
}
