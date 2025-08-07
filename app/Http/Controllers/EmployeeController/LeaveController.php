<?php

namespace App\Http\Controllers\EmployeeController;

use App\Roles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave;
use App\Models\User;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === Roles::Admin->value || $user->role === Roles::Approver->value) {
            // Admins and approvers can see all leaves they need to approve
            $leaves = Leave::where('approver_id', $user->id)
                ->orWhere('employee_id', $user->id)
                ->with(['employee', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Employees can only see their own leaves
            $leaves = Leave::where('employee_id', $user->id)
                ->with(['employee', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('Employee.leaves.leave-show', compact('leaves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all approvers for the dropdown
        $approvers = User::where('role', Roles::Approver->value)
            ->orWhere('role', Roles::Admin->value)
            ->get();
            
        return view('Employee.leaves.leave-request', compact('approvers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ]);
        
        $leave = new Leave();
        $leave->employee_id = Auth::id();
        $leave->approver_id = $request->approver_id;
        $leave->date_start = $request->date_start;
        $leave->date_end = $request->date_end;
        $leave->reason = $request->reason;
        $leave->status = 'pending';
        $leave->save();

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        // Check if the user has permission to view this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id && $user->id !== $leave->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }
        
        $leave->load(['employee', 'approver']);
        
        return view('Employee.leaves.leave-show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        // Check if the user has permission to edit this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow editing if the leave is still pending
        if ($leave->status !== 'pending') {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }
        
        $approvers = User::where('role', Roles::Approver->value)
            ->orWhere('role', Roles::Admin->value)
            ->get();
            
        return view('Employee.leaves.leave-edit', compact('leave', 'approvers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leave $leave)
    {
        // Check if the user has permission to update this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow updating if the leave is still pending
        if ($leave->status !== 'pending') {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot update a leave request that has already been processed.');
        }
        
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ]);
        
        $leave->approver_id = $request->approver_id;
        $leave->date_start = $request->date_start;
        $leave->date_end = $request->date_end;
        $leave->reason = $request->reason;
        $leave->save();
        
        return redirect()->route('employee.leaves.show', $leave->id)
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        // Check if the user has permission to delete this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow deleting if the leave is still pending
        if ($leave->status !== 'pending' && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }
        
        $leave->delete();
        
        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }
    
    /**
     * Show the form for reviewing a leave request.
     */
    public function review(Leave $leave)
    {
        // Check if the user has permission to review this leave
        $user = Auth::user();
        if ($user->id !== $leave->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }
        
        $leave->load(['employee', 'approver']);
        
        // return view('leaves.review', compact('leave'));
    }
    
    /**
     * Process the leave request approval or rejection.
     */
    public function processReview(Request $request, Leave $leave)
    {
        // Check if the user has permission to review this leave
        $user = Auth::user();
        if ($user->id !== $leave->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $leave->status = $request->status;
        // You might want to add a comment field to your leaves table
        // $leave->comment = $request->comment;
        $leave->save();

        // return redirect()->route('employee.leaves.review', $leave->id)
        //     ->with('success', 'Leave request has been ' . $request->status . '.');
    }
}
