<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Roles;
use App\Models\Reimbursement;
use App\Models\Overtime;
use App\Models\OfficialTravel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        
        // Get counts for pending requests
        $pendingLeaves = Leave::where('employee_id', $userId)
            ->where('status', 'pending')
            ->count();
            
        $pendingReimbursements = Reimbursement::where('employee_id', $userId)
            ->where('status', 'pending')
            ->count();
            
        $pendingOvertimes = Overtime::where('employee_id', $userId)
            ->where('status', 'pending')
            ->count();
            
        $pendingTravels = OfficialTravel::where('employee_id', $userId)
            ->where('status', 'pending')
            ->count();
        
        // Get recent requests (combined from all types)
        $recentRequests = $this->getRecentRequests($userId);
        
        // Get pending approvals for approvers and admins
        $pendingApprovals = [];
        if ($user->role === Roles::Approver->value || $user->role === Roles::Admin->value) {
            $pendingApprovals = $this->getPendingApprovals();
        }
        
        return view('Employee.index', compact(
            'pendingLeaves', 
            'pendingReimbursements', 
            'pendingOvertimes', 
            'pendingTravels', 
            'recentRequests',
            'pendingApprovals'
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
                    'type' => 'leave',
                    'title' => 'Leave Request: ' . Carbon::parse($leave->date_start)->format('M d') . ' - ' . Carbon::parse($leave->date_end)->format('M d'),
                    'date' => Carbon::parse($leave->created_at)->format('M d, Y'),
                    'status' => $leave->status,
                    'url' => route('employee.leaves.show', $leave->id)
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
                    'type' => 'reimbursement',
                    'title' => 'Reimbursement: Rp ' . number_format($reimbursement->total),
                    'date' => Carbon::parse($reimbursement->created_at)->format('M d, Y'),
                    'status' => $reimbursement->status,
                    'url' => route('employee.reimbursements.show', $reimbursement->id)
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
                    'type' => 'overtime',
                    'title' => 'Overtime: ' . Carbon::parse($overtime->date_start)->format('M d'),
                    'date' => Carbon::parse($overtime->created_at)->format('M d, Y'),
                    'status' => $overtime->status,
                    'url' => route('employee.overtimes.show', $overtime->id)
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
                    'type' => 'travel',
                    'title' => 'Official Travel: ' . Carbon::parse($travel->date_start)->format('M d') . ' - ' . Carbon::parse($travel->date_end)->format('M d'),
                    'date' => Carbon::parse($travel->created_at)->format('M d, Y'),
                    'status' => $travel->status,
                    'url' => route('employee.official-travels.show', $travel->id)
                ];
            });
            
        // Combine all requests and sort by date
        $allRequests = $leaves->concat($reimbursements)
            ->concat($overtimes)
            ->concat($travels)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->all();
            
        return $allRequests;
    }
    
    // private function getPendingApprovals()
    // {
    //     $user = Auth::user();
        
    //     // Get pending leaves
    //     $leaves = Leave::where('approver_id', $user->id)
    //         ->where('status', 'pending')
    //         ->with('employee')
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($leave) {
    //             return [
    //                 'id' => $leave->id,
    //                 'type' => 'leave',
    //                 'title' => 'Leave Request',
    //                 'employee' => $leave->employee->name,
    //                 'date' => Carbon::parse($leave->date_start)->format('M d') . ' - ' . Carbon::parse($leave->date_end)->format('M d'),
    //                 'url' => route('employee.leaves.review', $leave->id)
    //             ];
    //         });
            
    //     // Get pending reimbursements
    //     $reimbursements = Reimbursement::where('approver_id', $user->id)
    //         ->where('status', 'pending')
    //         ->with('employee')
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($reimbursement) {
    //             return [
    //                 'id' => $reimbursement->id,
    //                 'type' => 'reimbursement',
    //                 'title' => 'Reimbursement: Rp ' . number_format($reimbursement->total),
    //                 'employee' => $reimbursement->employee->name,
    //                 'date' => Carbon::parse($reimbursement->date)->format('M d, Y'),
    //                 'url' => route('employee.reimbursements.review', $reimbursement->id)
    //             ];
    //         });
            
    //     // Get pending overtimes
    //     $overtimes = Overtime::where('approver_id', $user->id)
    //         ->where('status', 'pending')
    //         ->with('employee')
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($overtime) {
    //             return [
    //                 'id' => $overtime->id,
    //                 'type' => 'overtime',
    //                 'title' => 'Overtime Request',
    //                 'employee' => $overtime->employee->name,
    //                 'date' => Carbon::parse($overtime->date_start)->format('M d'),
    //                 'url' => route('employee.overtimes.review', $overtime->id)
    //             ];
    //         });
            
    //     // Get pending official travels
    //     $travels = OfficialTravel::where('approver_id', $user->id)
    //         ->where('status', 'pending')
    //         ->with('employee')
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($travel) {
    //             return [
    //                 'id' => $travel->id,
    //                 'type' => 'travel',
    //                 'title' => 'Official Travel Request',
    //                 'employee' => $travel->employee->name,
    //                 'date' => Carbon::parse($travel->date_start)->format('M d') . ' - ' . Carbon::parse($travel->date_end)->format('M d'),
    //                 'url' => route('employee.official-travels.review', $travel->id)
    //             ];
    //         });
            
    //     // Combine all approvals
    //     $allApprovals = $leaves->concat($reimbursements)
    //         ->concat($overtimes)
    //         ->concat($travels)
    //         ->sortByDesc('date')
    //         ->values()
    //         ->all();
            
    //     return $allApprovals;
    // }
}
