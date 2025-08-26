<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Roles;
use App\TypeRequest;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        // --- Query untuk "Your Leaves"
        $yourLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $yourLeavesQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourLeavesQuery->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $yourLeavesQuery->where('status_1', $request->status);
        }

        $yourLeaves = $yourLeavesQuery->paginate(10, ['*'], 'your_page');


        // --- Query untuk "All Leaves"
        $allLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allLeavesQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allLeavesQuery->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allLeaves = $allLeavesQuery->paginate(10, ['*'], 'all_page');

        $counts = (clone $allLeavesQuery)->withFinalStatusCount()->first();
        $totalRequests = Leave::count();
        $approvedRequests = (int) $counts->approved;

        $countsYours = (clone $yourLeavesQuery)->withFinalStatusCount()->first();

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) $countsYours->approved;
        $totalYoursRequests = (int) $yourLeavesQuery->count();
        $pendingYoursRequests = (int) $countsYours->pending;
        $approvedYoursRequests = (int) $countsYours->approved;
        $rejectedYoursRequests = (int) $countsYours->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.leaves.leave-show', compact(
            'yourLeaves',
            'allLeaves',
            'totalRequests',
            'approvedRequests',
            'manager',
            'sisaCuti',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        $leave->load(['employee', 'approver']);
        return view('Finance.leaves.leave-detail', compact('leave'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Finance.leaves.pdf', compact('leave'));
        return $pdf->download('leave-details-finance.pdf');
    }
}
