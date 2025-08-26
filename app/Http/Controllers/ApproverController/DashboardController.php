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
            $base = $model::query()->where('created_at', '>=', $startOfMonth);

            // Aman untuk single/dual status karena pakai scope trait
            $pendings[$key] = (clone $base)->filterFinalStatus('pending')->forLeader(Auth::id())->count();
            $rejecteds[$key] = (clone $base)->filterFinalStatus('rejected')->forLeader(Auth::id())->count();
            $approveds[$key] = (clone $base)->filterFinalStatus('approved')->forLeader(AUth::id())->count();
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
