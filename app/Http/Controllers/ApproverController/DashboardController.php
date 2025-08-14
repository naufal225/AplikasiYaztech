<?php

namespace App\Http\Controllers\ApproverController;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\OfficialTravel;
use App\Models\Overtime;
use App\Models\Reimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $models = [
            "reimbursements" => Reimbursement::class,
            "overtimes" => Overtime::class,
            "leaves" => Leave::class,
            "official_travels" => OfficialTravel::class,
        ];

        $startOfMonth = Carbon::now()->startOfMonth();
        $pendings = $approveds = $rejecteds = [];


        foreach ($models as $key => $model) {
            $table = (new $model)->getTable();

            // Klasifikasi final_status berdasar dua kolom status
            $caseExpr = "
                CASE
                    WHEN {$table}.status_1 = 'rejected' OR {$table}.status_2 = 'rejected'
                        THEN 'rejected'
                    WHEN {$table}.status_1 = 'pending' OR {$table}.status_2 = 'pending'
                        THEN 'pending'
                    WHEN {$table}.status_1 = 'approved' AND {$table}.status_2 = 'approved'
                        THEN 'approved'
                    ELSE 'unknown'
                END AS final_status
            ";

            $counts = $model::query()
                ->selectRaw("$caseExpr, COUNT(*) AS aggregate")
                ->whereHas('employee.division', function ($q) {
                    $q->where('leader_id', Auth::id());
                })
                ->where("{$table}.created_at", '>=', $startOfMonth)
                ->groupBy('final_status')
                ->pluck('aggregate', 'final_status');

            $pendings[$key] = (int) ($counts['pending'] ?? 0);
            $approveds[$key] = (int) ($counts['approved'] ?? 0);
            $rejecteds[$key] = (int) ($counts['rejected'] ?? 0);
        }

        $total_pending = array_sum($pendings);
        $total_rejected = array_sum($rejecteds);
        $total_approved = array_sum($approveds);

        // Generate chart data per bulan
        $reimbursementsChartData = $overtimesChartData = $leavesChartData = $officialTravelsChartData = $reimbursementsRupiahChartData = [];
        $months = [];

        $year = now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::create($year, $i, 1);
            $monthName = $date->translatedFormat('F');
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $months[] = $monthName;
            $reimbursementsChartData[] = Reimbursement::whereHas('employee.division', function ($q) {
                $q->where('leader_id', Auth::id());
            })->whereBetween('created_at', [$start, $end])->count();
            $reimbursementsRupiahChartData[] = Reimbursement::whereHas('employee.division', function ($q) {
                $q->where('leader_id', Auth::id());
            })->whereBetween('created_at', [$start, $end])->sum('total');
            $overtimesChartData[] = Overtime::whereHas('employee.division', function ($q) {
                $q->where('leader_id', Auth::id());
            })->whereBetween('created_at', [$start, $end])->count();
            $leavesChartData[] = Leave::whereHas('employee.division', function ($q) {
                $q->where('leader_id', Auth::id());
            })->whereBetween('created_at', [$start, $end])->count();
            $officialTravelsChartData[] = OfficialTravel::whereHas('employee.division', function ($q) {
                $q->where('leader_id', Auth::id());
            })->whereBetween('created_at', [$start, $end])->count();
        }


        return view('approver.dashboard.index', compact([
            'total_pending',
            'total_approved',
            'total_rejected',
            'reimbursementsChartData',
            'overtimesChartData',
            'leavesChartData',
            'officialTravelsChartData',
            'months',
            'reimbursementsRupiahChartData'
        ]));
    }
}
