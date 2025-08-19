<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Roles;
use App\TypeRequest;
use App\Models\OfficialTravel;
use App\Models\User;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OfficialTravelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryReal = OfficialTravel::whereHas('employee', function ($q) {
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
            $query->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $officialTravels = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryReal->count();
        $approvedRequests = (int) $counts->approved;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.travels.travel-show', compact('officialTravels', 'totalRequests', 'approvedRequests', 'manager'));
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficialTravel $officialTravel)
    {
        $officialTravel->load(['employee', 'approver']);
        return view('Finance.travels.travel-detail', compact('officialTravel'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(OfficialTravel $officialTravel)
    {
        $pdf = Pdf::loadView('Finance.travels.pdf', compact('officialTravel'));
        return $pdf->download('official-travel-details-finance.pdf');
    }
}
