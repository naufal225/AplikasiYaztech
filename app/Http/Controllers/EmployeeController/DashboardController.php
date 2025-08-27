<?php

namespace App\Http\Controllers\EmployeeController;

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
        $user = Auth::user();
        $userId = $user->id;

        $employeeCount = User::where('role', Roles::Employee->value)->count();

        $queryLeave = Leave::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        $queryClone = (clone $queryLeave);

        $queryReimbursement = Reimbursement::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        $queryOvertime = Overtime::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        $queryTravel = OfficialTravel::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        // Get counts for pending requests
        $pendingLeaves = $queryLeave->withFinalStatusCount()->first()->pending;
        $pendingReimbursements = $queryReimbursement->withFinalStatusCount()->first()->pending;
        $pendingOvertimes = $queryOvertime->withFinalStatusCount()->first()->pending;
        $pendingTravels = $queryTravel->withFinalStatusCount()->first()->pending;

        // Get counts for approve requests
        $approvedLeaves = $queryLeave->withFinalStatusCount()->first()->approved;
        $approvedReimbursements = $queryReimbursement->withFinalStatusCount()->first()->approved;
        $approvedOvertimes = $queryOvertime->withFinalStatusCount()->first()->approved;
        $approvedTravels = $queryTravel->withFinalStatusCount()->first()->approved;

        // Get counts for rejected requests
        $rejectedLeaves = $queryLeave->withFinalStatusCount()->first()->rejected;
        $rejectedReimbursements = $queryReimbursement->withFinalStatusCount()->first()->rejected;
        $rejectedOvertimes = $queryOvertime->withFinalStatusCount()->first()->rejected;
        $rejectedTravels = $queryTravel->withFinalStatusCount()->first()->rejected;

        // Get recent requests (combined from all types)
        $recentRequests = $this->getRecentRequests($userId);

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) $queryClone->where('status_1', 'approved')->whereYear('date_start', now()->year)->count();
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
                ];
                $start->addDay();
            }
        }

        return view('Employee.index', compact(
            'employeeCount', 'pendingLeaves', 'pendingReimbursements', 'pendingOvertimes',
            'pendingTravels', 'recentRequests', 'approvedLeaves', 'approvedReimbursements',
            'approvedOvertimes', 'approvedTravels', 'rejectedLeaves', 'rejectedReimbursements',
            'rejectedOvertimes', 'rejectedTravels', 'sisaCuti', 'cutiPerTanggal'
        ));
    }

    private function getRecentRequests($userId)
    {
        // Get recent leaves
        $leaves = Leave::where('employee_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'type' => TypeRequest::Leaves->value,
                    'title' => 'Leave Request: ' . Carbon::parse($leave->date_start)->format('M d') . ' - ' . Carbon::parse($leave->date_end)->format('M d'),
                    'date' => Carbon::parse($leave->created_at)->format('M d, Y'),
                    'status_1' => $leave->status_1,
                    'url' => route('employee.leaves.show', $leave->id),
                    'created_at' => $leave->created_at
                ];
            });

        // Get recent reimbursements
        $reimbursements = Reimbursement::where('employee_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reimbursement) {
                return [
                    'id' => $reimbursement->id,
                    'type' => TypeRequest::Reimbursements->value,
                    'title' => 'Reimbursement: Rp ' . number_format($reimbursement->total),
                    'date' => Carbon::parse($reimbursement->created_at)->format('M d, Y'),
                    'status_1' => $reimbursement->status_1,
                    'status_2' => $reimbursement->status_2,
                    'url' => route('employee.reimbursements.show', $reimbursement->id),
                    'created_at' => $reimbursement->created_at
                ];
            });

        // Get recent overtimes
        $overtimes = Overtime::where('employee_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($overtime) {
                return [
                    'id' => $overtime->id,
                    'type' => TypeRequest::Overtimes->value,
                    'title' => 'Overtime: ' . Carbon::parse($overtime->date_start)->format('M d'),
                    'date' => Carbon::parse($overtime->created_at)->format('M d, Y'),
                    'status_1' => $overtime->status_1,
                    'status_2' => $overtime->status_2,
                    'url' => route('employee.overtimes.show', $overtime->id),
                    'created_at' => $overtime->created_at
                ];
            });

        // Get recent official travels
        $travels = OfficialTravel::where('employee_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($travel) {
                return [
                    'id' => $travel->id,
                    'type' => TypeRequest::Travels->value,
                    'title' => 'Official Travel: ' . Carbon::parse($travel->date_start)->format('M d') . ' - ' . Carbon::parse($travel->date_end)->format('M d'),
                    'date' => Carbon::parse($travel->created_at)->format('M d, Y'),
                    'status_1' => $travel->status_1,
                    'status_2' => $travel->status_2,
                    'url' => route('employee.official-travels.show', $travel->id),
                    'created_at' => $travel->created_at
                ];
            });

        // Combine all requests and sort by date
        $allRequests = $leaves->concat($reimbursements)
            ->concat($overtimes)
            ->concat($travels)
            ->sortByDesc('created_at')
            ->take(8)
            ->values()
            ->all();

        return $allRequests;
    }
}
