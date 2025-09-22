<?php

namespace App\Http\Controllers\ApproverController;

use App\Http\Controllers\Controller;
use App\Traits\HelperController;
use App\Models\Leave;
use App\Models\OfficialTravel;
use App\Models\Overtime;
use App\Models\Reimbursement;
use App\Models\User;
use App\Enums\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use HelperController;

    public function index()
    {
        $models = [
            "reimbursements" => Reimbursement::class,
            "overtimes" => Overtime::class,
            "leaves" => Leave::class,
            "official_travels" => OfficialTravel::class
        ];

        $startOfMonth = Carbon::now()->startOfMonth();
        $pendings = $approveds = $rejecteds = [];

        foreach ($models as $key => $model) {
            $base = $model::query()->where('created_at', '>=', $startOfMonth);

            // Tambahkan scope forLeader untuk filter berdasarkan divisi leader
            $pendings[$key] = (clone $base)->filterFinalStatus('pending')->forLeader(Auth::id())->count();
            $rejecteds[$key] = (clone $base)->filterFinalStatus('rejected')->forLeader(Auth::id())->count();
            $approveds[$key] = (clone $base)->filterFinalStatus('approved')->forLeader(Auth::id())->count();
        }

        $total_pending = array_sum($pendings);
        $total_rejected = array_sum($rejecteds);
        $total_approved = array_sum($approveds);
        $total_employees = User::whereHas('division', function ($q) use ($models) {
            $q->where('leader_id', Auth::id());
        })->where('role', Roles::Employee->value)->count();

        // Generate chart data per bulan dengan filter forLeader
        $reimbursementsChartData = $overtimesChartData = $leavesChartData = $officialTravelsChartData = $reimbursementsRupiahChartData = [];
        $months = [];

        $year = now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::create($year, $i, 1);
            $monthName = $date->translatedFormat('F');
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $months[] = $monthName;

            // Tambahkan scope forLeader untuk semua query chart
            $reimbursementsChartData[] = Reimbursement::forLeader(Auth::id())->whereBetween('created_at', [$start, $end])->count();
            $reimbursementsRupiahChartData[] = Reimbursement::forLeader(Auth::id())->whereBetween('created_at', [$start, $end])->sum('total');
            $overtimesChartData[] = Overtime::forLeader(Auth::id())->whereBetween('created_at', [$start, $end])->count();
            $leavesChartData[] = Leave::forLeader(Auth::id())->whereBetween('created_at', [$start, $end])->count();
            $officialTravelsChartData[] = OfficialTravel::forLeader(Auth::id())->whereBetween('created_at', [$start, $end])->count();
        }

        // Sisa cuti untuk approver (jika needed)
        $sisaCuti = 0; // Approver mungkin tidak perlu sisa cuti, tapi bisa diisi jika diperlukan

        // Recent requests dengan filter forLeader
        $recentRequests = $this->getRecentRequestsForLeader(Auth::id());

        // Data cuti per tanggal untuk kalender (hanya dari divisi leader)
        $cutiPerTanggal = [];

        $karyawanCuti = Leave::with(['employee:id,name,email,url_profile'])
            ->forLeader(Auth::id())
            ->where('status_1', 'approved')
            ->where(function ($q) {
                $q->whereYear('date_start', now()->year)
                    ->orWhereYear('date_end', now()->year);
            })
            ->get(['id', 'employee_id', 'date_start', 'date_end']);

        foreach ($karyawanCuti as $cuti) {
            $start = Carbon::parse($cuti->date_start);
            $end = Carbon::parse($cuti->date_end);
            while ($start->lte($end)) {
                $tanggal = $start->format('Y-m-d');
                $cutiPerTanggal[$tanggal][] = [
                    'employee' => $cuti->employee->name,
                    'email' => $cuti->employee->email,
                    'url_profile' => $cuti->employee->url_profile,
                ];
                $start->addDay();
            }
        }

        return view('approver.dashboard.index', compact([
            'total_employees',
            'total_pending',
            'total_approved',
            'total_rejected',
            'reimbursementsChartData',
            'overtimesChartData',
            'leavesChartData',
            'officialTravelsChartData',
            'months',
            'reimbursementsRupiahChartData',
            'sisaCuti',
            'recentRequests',
            'cutiPerTanggal'
        ]));
    }

    // Method khusus untuk mendapatkan recent requests dengan filter leader
    private function getRecentRequestsForLeader($leaderId)
    {
        // Implementasi similar to HelperController but with forLeader scope
        $recentLeaves = Leave::forLeader($leaderId)
            ->with(['employee:id,name,division_id,url_profile', 'employee.division:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => \App\Enums\TypeRequest::Leaves->value,
                    'title' => 'Leave Request',
                    'employee_name' => $item->employee->name,
                    'division_name' => $item->employee->division->name,
                    'url_profile' => $item->employee->url_profile,
                    'date' => $item->created_at->format('M d, Y'),
                    'status_1' => $item->status_1,
                    'status_2' => $item->status_2,
                    'url' => route('approver.leaves.show', $item->id)
                ];
            });

        $recentReimbursements = Reimbursement::forLeader($leaderId)
            ->with(['employee:id,name,division_id,url_profile', 'employee.division:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => \App\Enums\TypeRequest::Reimbursements->value,
                    'title' => 'Reimbursement Request',
                    'employee_name' => $item->employee->name,
                    'division_name' => $item->employee->division->name,
                    'url_profile' => $item->employee->url_profile,
                    'date' => $item->created_at->format('M d, Y'),
                    'status_1' => $item->status_1,
                    'status_2' => $item->status_2,
                    'url' => route('approver.reimbursements.show', $item->id)
                ];
            });

        $recentOvertimes = Overtime::forLeader($leaderId)
            ->with(['employee:id,name,division_id,url_profile', 'employee.division:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => \App\Enums\TypeRequest::Overtimes->value,
                    'title' => 'Overtime Request',
                    'employee_name' => $item->employee->name,
                    'division_name' => $item->employee->division->name,
                    'url_profile' => $item->employee->url_profile,
                    'date' => $item->created_at->format('M d, Y'),
                    'status_1' => $item->status_1,
                    'status_2' => $item->status_2,
                    'url' => route('approver.overtimes.show', $item->id)
                ];
            });

        $recentTravels = OfficialTravel::forLeader($leaderId)
            ->with(['employee:id,name,division_id,url_profile', 'employee.division:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => \App\Enums\TypeRequest::Travels->value,
                    'title' => 'Official Travel Request',
                    'employee_name' => $item->employee->name,
                    'division_name' => $item->employee->division->name,
                    'url_profile' => $item->employee->url_profile,
                    'date' => $item->created_at->format('M d, Y'),
                    'status_1' => $item->status_1,
                    'status_2' => $item->status_2,
                    'url' => route('approver.official-travels.show', $item->id)
                ];
            });

        return $recentLeaves->concat($recentReimbursements)
            ->concat($recentOvertimes)
            ->concat($recentTravels)
            ->sortByDesc('date')
            ->take(5)
            ->values();
    }
}
