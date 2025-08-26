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
        $queryReal = Leave::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            });

        $query = (clone $queryReal)
            ->where('status_1', 'approved')
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        $queryClone = (clone $query);

        if ($request->filled('from_date')) {
            $query->where('date_start', '>=',
                Carbon::parse($request->from_date)
                    ->startOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where('date_start', '<=',
                Carbon::parse($request->to_date)
                    ->endOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        $leaves = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryReal->count();
        $approvedRequests = (int) $counts->approved;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.leaves.leave-show', compact('leaves', 'totalRequests', 'approvedRequests', 'manager'));
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
