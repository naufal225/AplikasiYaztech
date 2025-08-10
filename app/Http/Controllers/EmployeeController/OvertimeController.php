<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Roles;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Overtime::where('employee_id', $user->id)
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
            $query->where('date_end', '<=', 
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $overtimes = $query->paginate(10);
        $totalRequests = Overtime::where('employee_id', $user->id)->count();
        $pendingRequests = Overtime::where('employee_id', $user->id)->where('status', 'pending')->count();
        $approvedRequests = Overtime::where('employee_id', $user->id)->where('status', 'approved')->count();
        $rejectedRequests = Overtime::where('employee_id', $user->id)->where('status', 'rejected')->count();

        return view('Employee.overtimes.overtime-show', compact('overtimes', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Employee.overtimes.overtime-request', compact('approvers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'approver_id' => 'required|exists:users,id',
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ]);

        // 1 Parse dengan timezone eksplisit
        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        // 2: Set jam kerja normal
        $workEnd = (clone $start)->setTime(17, 0, 0);

        // 3: Hitung overtime dengan benar
        $overtimeMinutes = max(0, $workEnd->diffInMinutes($end, false));
        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        Overtime::create([
            'employee_id' => Auth::id(),
            'approver_id' => $request->approver_id,
            'date_start' => $start,
            'date_end' => $end,
            'total' => $overtimeMinutes,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime submitted. Total: '. number_format($overtimeHours, 2) .' hours');
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
        return view('Employee.overtimes.overtime-detail', compact('overtime'));
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
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ]);

        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        // 2: Set jam kerja normal
        $workEnd = (clone $start)->setTime(17, 0, 0);

        // 3: Hitung overtime dengan benar
        $overtimeMinutes = max(0, $workEnd->diffInMinutes($end, false));
        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        $overtime->approver_id = $request->approver_id;
        $overtime->date_start = $request->date_start;
        $overtime->date_end = $request->date_end;
        $overtime->total = $overtimeMinutes;
        $overtime->save();

        return redirect()->route('employee.overtimes.show', $overtime->id)
            ->with('success', 'Overtime request updated successfully. Total overtime: ' . number_format($overtimeHours, 2) . ' hours');
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

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
    }
}