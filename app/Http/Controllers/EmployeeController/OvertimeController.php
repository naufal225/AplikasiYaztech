<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Overtime;
use App\Models\User;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $overtimes = Overtime::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('Employee.overtimes.overtime-show', compact('overtimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->orWhere('role', Roles::Admin->value)
            ->get();
        return view('Employee.overtimes.overtime-request', compact('approvers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'reason' => 'required|string|max:1000',
        ]);

        $overtime = new Overtime();
        $overtime->employee_id = Auth::id();
        $overtime->approver_id = $request->approver_id;
        $overtime->date = $request->date;
        $overtime->hours = $request->hours;
        $overtime->reason = $request->reason;
        $overtime->status = 'pending';
        $overtime->save();

        return redirect()->route('employee.overtimes.show')
            ->with('success', 'Overtime request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id && $user->id !== $overtime->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        $overtime->load(['employee', 'approver']);
        return view('Employee.overtimes.overtime-show', compact('overtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status !== 'pending') {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot edit an overtime request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->orWhere('role', Roles::Admin->value)
            ->get();
        return view('Employee.overtimes.overtime-edit', compact('overtime', 'approvers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status !== 'pending') {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot update an overtime request that has already been processed.');
        }

        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'reason' => 'required|string|max:1000',
        ]);

        $overtime->approver_id = $request->approver_id;
        $overtime->date = $request->date;
        $overtime->hours = $request->hours;
        $overtime->reason = $request->reason;
        $overtime->save();

        return redirect()->route('employee.overtimes.show', $overtime->id)
            ->with('success', 'Overtime request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status !== 'pending' && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot delete an overtime request that has already been processed.');
        }

        $overtime->delete();
        return redirect()->route('employee.overtimes.show')
            ->with('success', 'Overtime request deleted successfully.');
    }

    /**
     * Show the form for reviewing an overtime request.
     */
    public function review(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        $overtime->load(['employee', 'approver']);
        return view('Employee.overtimes.overtime-review', compact('overtime'));
    }

    /**
     * Process the overtime request approval or rejection.
     */
    public function processReview(Request $request, Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);

        $overtime->status = $request->status;
        // Anda mungkin ingin menambahkan kolom komentar ke tabel overtimes Anda
        // $overtime->comment = $request->comment;
        $overtime->save();

        return redirect()->route('employee.overtimes.review', $overtime->id)
            ->with('success', 'Overtime request has been ' . $request->status . '.');
    }
}