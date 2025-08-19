<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Roles;
use App\TypeRequest;
use App\Models\Overtime;
use App\Models\User;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $queryReal = Overtime::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            });

        $query = (clone $queryReal)
            ->where('status_1', 'approved')
            ->where('status_2', 'approved')
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        $queryClone = (clone $query);

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
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryReal->count();
        $approvedRequests = (int) $counts->approved;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.overtimes.overtime-show', compact('overtimes', 'totalRequests', 'approvedRequests', 'manager'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        $overtime->load(['employee', 'approver']);
        return view('Finance.overtimes.overtime-detail', compact('overtime'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Overtime $overtime)
    {
        $pdf = Pdf::loadView('Finance.overtimes.pdf', compact('overtime'));
        return $pdf->download('overtime-details-finance.pdf');
    }
}
