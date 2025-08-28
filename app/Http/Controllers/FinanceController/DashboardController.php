<?php

namespace App\Http\Controllers\FinanceController;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Roles;
use App\TypeRequest;
use App\Models\Reimbursement;
use App\Models\Overtime;
use App\Models\OfficialTravel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Pastikan hanya role finance yang bisa masuk sini
        if (Auth::user()->role !== Roles::Finance->value) {
            abort(403, 'Unauthorized');
        }

        // =========================
        // DATA COUNT (CARD)
        // =========================
        $leaveCount = Leave::whereHas('employee', function ($q) {
            $q->where('role', Roles::Employee->value);
        })->where('status_1', 'approved')->count();

        $overtimeCount = Overtime::whereHas('employee', function ($q) {
            $q->where('role', Roles::Employee->value);
        })->where('status_1', 'approved')->where('status_2', 'approved')->count();

        $reimbursementCount = Reimbursement::whereHas('employee', function ($q) {
            $q->where('role', Roles::Employee->value);
        })->where('status_1', 'approved')->where('status_2', 'approved')->count();

        $officialTravelCount = OfficialTravel::whereHas('employee', function ($q) {
            $q->where('role', Roles::Employee->value);
        })->where('status_1', 'approved')->where('status_2', 'approved')->count();

        // =========================
        // DATA UNTUK CHART (BULANAN)
        // =========================
        $months = collect(range(1, 12))->map(function ($m) {
            return Carbon::create()->month($m)->format('M');
        });

        $recentRequests = $this->getRecentRequests();

        $leavesChartData = [];
        $overtimesChartData = [];
        $reimbursementsChartData = [];
        $reimbursementsRupiahChartData = [];
        $officialTravelsChartData = [];

        foreach (range(1, 12) as $month) {
            $start = Carbon::create(null, $month, 1)->startOfMonth();
            $end   = Carbon::create(null, $month, 1)->endOfMonth();

            $leavesChartData[] = Leave::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })->where('status_1', 'approved')->whereBetween('created_at', [$start, $end])->count();

            $overtimesChartData[] = Overtime::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })->where('status_1', 'approved')->where('status_2', 'approved')->whereBetween('created_at', [$start, $end])->count();

            $reimbursementsChartData[] = Reimbursement::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })->where('status_1', 'approved')->where('status_2', 'approved')->whereBetween('created_at', [$start, $end])->count();

            $reimbursementsRupiahChartData[] = Reimbursement::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })->where('status_1', 'approved')->where('status_2', 'approved')->whereBetween('created_at', [$start, $end])->sum('total');

            $officialTravelsChartData[] = OfficialTravel::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })->where('status_1', 'approved')->where('status_2', 'approved')->whereBetween('created_at', [$start, $end])->count();
        }

        $karyawanCuti = Leave::with(['employee:id,name,email,url_profile'])
            ->where('status_1', 'approved')
            ->where(function ($q) {
                $q->whereYear('date_start', now()->year)
                ->orWhereYear('date_end', now()->year);
            })
            ->get(['id','employee_id','date_start','date_end']);

        $cutiPerTanggal = [];

        foreach ($karyawanCuti as $cuti) {
            $start = \Carbon\Carbon::parse($cuti->date_start);
            $end = \Carbon\Carbon::parse($cuti->date_end);
            while ($start->lte($end)) {
                $tanggal = $start->format('Y-m-d');
                $cutiPerTanggal[$tanggal][] = [
                    'employee' => $cuti->employee->name,
                    'email'    => $cuti->employee->email,
                    'url_profile' => $cuti->employee->url_profile,
                ];
                $start->addDay();
            }
        }

        return view('Finance.index', [
            'leaveCount' => $leaveCount,
            'overtimeCount' => $overtimeCount,
            'reimbursementCount' => $reimbursementCount,
            'officialTravelCount' => $officialTravelCount,
            'months' => $months,
            'leavesChartData' => $leavesChartData,
            'overtimesChartData' => $overtimesChartData,
            'reimbursementsChartData' => $reimbursementsChartData,
            'reimbursementsRupiahChartData' => $reimbursementsRupiahChartData,
            'officialTravelsChartData' => $officialTravelsChartData,
            'recentRequests' => $recentRequests,
            'cutiPerTanggal' => $cutiPerTanggal,
        ]);
    }

    private function getRecentRequests()
    {
        // Get recent leaves
        $leaves = Leave::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })
            ->where('status_1', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'type' => TypeRequest::Leaves->value,
                    'title' => 'Leave Request: ' . Carbon::parse($leave->date_start)->format('M d') . ' - ' . Carbon::parse($leave->date_end)->format('M d'),
                    'name_owner' => $leave->employee->name,
                    'email_owner' => $leave->employee->email,
                    'url_photo' => $leave->employee->url_profile,
                    'date' => Carbon::parse($leave->created_at)->format('M d, Y'),
                    'status_1' => $leave->status_1,
                    'url' => route('finance.leaves.show', $leave->id),
                    'created_at' => $leave->created_at
                ];
            });

        // Get recent reimbursements
        $reimbursements = Reimbursement::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })
            ->where('status_1', 'approved')->where('status_2', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reimbursement) {
                return [
                    'id' => $reimbursement->id,
                    'type' => TypeRequest::Reimbursements->value,
                    'title' => 'Reimbursement: Rp ' . number_format($reimbursement->total),
                    'name_owner' => $reimbursement->employee->name,
                    'email_owner' => $reimbursement->employee->email,
                    'url_photo' => $reimbursement->employee->url_profile,
                    'date' => Carbon::parse($reimbursement->created_at)->format('M d, Y'),
                    'status_1' => $reimbursement->status_1,
                    'status_2' => $reimbursement->status_2,
                    'url' => route('finance.reimbursements.show', $reimbursement->id),
                    'created_at' => $reimbursement->created_at
                ];
            });

        // Get recent overtimes
        $overtimes = Overtime::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })
            ->where('status_1', 'approved')->where('status_2', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($overtime) {
                return [
                    'id' => $overtime->id,
                    'type' => TypeRequest::Overtimes->value,
                    'title' => 'Overtime: ' . Carbon::parse($overtime->date_start)->format('M d'),
                    'date' => Carbon::parse($overtime->created_at)->format('M d, Y'),
                    'name_owner' => $overtime->employee->name,
                    'email_owner' => $overtime->employee->email,
                    'url_photo' => $overtime->employee->url_profile,
                    'status_1' => $overtime->status_1,
                    'status_2' => $overtime->status_2,
                    'url' => route('finance.overtimes.show', $overtime->id),
                    'created_at' => $overtime->created_at
                ];
            });

        // Get recent official travels
        $travels = OfficialTravel::whereHas('employee', function ($q) {
                $q->where('role', Roles::Employee->value);
            })
            ->where('status_1', 'approved')->where('status_2', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($travel) {
                return [
                    'id' => $travel->id,
                    'type' => TypeRequest::Travels->value,
                    'title' => 'Official Travel: ' . Carbon::parse($travel->date_start)->format('M d') . ' - ' . Carbon::parse($travel->date_end)->format('M d'),
                    'name_owner' => $travel->employee->name,
                    'email_owner' => $travel->employee->email,
                    'url_photo' => $travel->employee->url_profile,
                    'date' => Carbon::parse($travel->created_at)->format('M d, Y'),
                    'status_1' => $travel->status_1,
                    'status_2' => $travel->status_2,
                    'url' => route('finance.official-travels.show', $travel->id),
                    'created_at' => $travel->created_at
                ];
            });

        // Combine all requests and sort by date
        $allRequests = $leaves->concat($reimbursements)
            ->concat($overtimes)
            ->concat($travels)
            ->sortByDesc('created_at')
            ->take(4)
            ->values()
            ->all();

        return $allRequests;
    }
}
