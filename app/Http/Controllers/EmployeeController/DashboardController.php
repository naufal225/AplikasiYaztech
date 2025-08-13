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

        // Get counts for pending requests
        $pendingLeaves = $this->getUserRequestCounts($userId, "pending", TypeRequest::Leaves->value);
        $pendingReimbursements = $this->getUserRequestCounts($userId, "pending", TypeRequest::Reimbursements->value);
        $pendingOvertimes = $this->getUserRequestCounts($userId, "pending", TypeRequest::Overtimes->value);
        $pendingTravels = $this->getUserRequestCounts($userId, "pending", TypeRequest::Travels->value);

        // Get counts for approve requests
        $approvedLeaves = $this->getUserRequestCounts($userId, "approved", TypeRequest::Leaves->value, true);
        $approvedReimbursements = $this->getUserRequestCounts($userId, "approved", TypeRequest::Reimbursements->value, true);
        $approvedOvertimes = $this->getUserRequestCounts($userId, "approved", TypeRequest::Overtimes->value, true);
        $approvedTravels = $this->getUserRequestCounts($userId, "approved", TypeRequest::Travels->value, true);

        // Get counts for rejected requests
        $rejectedLeaves = $this->getUserRequestCounts($userId, "rejected", TypeRequest::Leaves->value, true);
        $rejectedReimbursements = $this->getUserRequestCounts($userId, "rejected", TypeRequest::Reimbursements->value, true);
        $rejectedOvertimes = $this->getUserRequestCounts($userId, "rejected", TypeRequest::Overtimes->value, true);
        $rejectedTravels = $this->getUserRequestCounts($userId, "rejected", TypeRequest::Travels->value, true);

        // Get recent requests (combined from all types)
        $recentRequests = $this->getRecentRequests($userId);

        return view('Employee.index', compact(
            'employeeCount', 'pendingLeaves', 'pendingReimbursements', 'pendingOvertimes',
            'pendingTravels', 'recentRequests', 'approvedLeaves', 'approvedReimbursements',
            'approvedOvertimes', 'approvedTravels', 'rejectedLeaves', 'rejectedReimbursements',
            'rejectedOvertimes', 'rejectedTravels'
        ));
    }

    private function getUserRequestCounts($userId, $status = 'pending', $type = "cuti", $thisMonth = false): String {
        switch ($type) {
            case TypeRequest::Leaves->value:
                return $thisMonth ? Leave::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->whereMonth('created_at', Carbon::now()->month)->count() : Leave::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->count();
                break;
            case TypeRequest::Reimbursements->value:
                return $thisMonth ? Reimbursement::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->whereMonth('created_at', Carbon::now()->month)->count() : Reimbursement::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->count();
                break;
            case TypeRequest::Overtimes->value:
                return $thisMonth ? Overtime::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->whereMonth('created_at', Carbon::now()->month)->count() : Overtime::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->count();
                break;
            case TypeRequest::Travels->value:
                return $thisMonth ? OfficialTravel::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->whereMonth('created_at', Carbon::now()->month)->count() : OfficialTravel::where('employee_id', $userId)->where('status_1', $status)->orWhere('status_2', $status)->count();
                break;
            default:
                return 0;
        }
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
                    'status_2' => $leave->status_2,
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
            ->take(10)
            ->values()
            ->all();

        return $allRequests;
    }
}
