<?php

namespace App\Http\Controllers\EmployeeController;

use App\Roles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Leave::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        $leaves = $query->paginate(10);
        $totalRequests = Leave::where('employee_id', $user->id)->count();
        $pendingRequests = Leave::where('employee_id', $user->id)->where('status', 'pending')->count();
        $approvedRequests = Leave::where('employee_id', $user->id)->where('status', 'approved')->count();
        $rejectedRequests = Leave::where('employee_id', $user->id)->where('status', 'rejected')->count();

        return view('Employee.leaves.leave-show', compact('leaves', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all approvers for the dropdown
        $approvers = User::where('role', Roles::Approver->value)
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

        // Send notification email to the approver
        $approver = User::find($request->approver_id);
        if ($approver) {
            $linkTanggapan = route('employee.leaves.show', $leave->id);
            $pesan = "Pengajuan cuti baru dari " . Auth::user()->name . ". <br> Tanggal mulai: {$request->date_start} <br> Tanggal selesai: {$request->date_end} <br> Alasan: {$request->reason}";

            Mail::to($approver->email)->send(new \App\Mail\SendMessage(
                namaPengaju: Auth::user()->name,
                pesan: $pesan,
                namaApprover: $approver->name,
                linkTanggapan: $linkTanggapan,
                emailPengaju: Auth::user()->email
            ));
        }

        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Check if the user has permission to view this leave
        $user = Auth::user();
        $leave = \App\Models\Leave::find($id);

        if ($user->id !== $leave->employee_id && $user->id !== $leave->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        $leave->load(['employee', 'approver']);
        return view('Employee.leaves.leave-detail', compact('leave'));
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
            'approver_id' => 'required|exists:users,id', // Validasi approver_id
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ]);

        $leave->approver_id = $request->approver_id; // Memperbarui approver_id
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
}
