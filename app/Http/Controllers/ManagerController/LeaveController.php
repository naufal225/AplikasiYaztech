<?php

namespace App\Http\Controllers\ManagerController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLeaveRequest;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Models\ApprovalLink;
use App\Models\Leave;
use App\Models\User;
use App\Roles;
use App\Services\LeaveApprovalService;
use App\Services\LeaveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LeaveController extends Controller
{
    public function index(Request $request)
    {

        // Query for user's own requests (all statuses)
        $ownRequestsQuery = Leave::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = Leave::with(['employee', 'approver'])
            ->where(function ($q) {
                $q->where('employee_id', '!=', Auth::id())
                    ->orWhere(function ($subQ) {
                        $subQ->where('employee_id', Auth::id())
                            ->where('status_1', 'approved');
                    });
            })
            ->orderBy('created_at', 'desc');

        // Apply filters to both queries
        if ($request->filled('status')) {
            $statusFilter = function ($query) use ($request) {
                switch ($request->status) {
                    case 'approved':
                        $query->where('status_1', 'approved');
                        break;
                    case 'rejected':
                        $query->where('status_1', 'rejected');
                        break;
                    case 'pending':
                        $query->where('status_1', 'pending');
                        break;
                }
            };

            $ownRequestsQuery->where($statusFilter);
            $allUsersQuery->where($statusFilter);
        }

        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta');
            $ownRequestsQuery->where('created_at', '>=', $fromDate);
            $allUsersQuery->where('created_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta');
            $ownRequestsQuery->where('created_at', '<=', $toDate);
            $allUsersQuery->where('created_at', '<=', $toDate);
        }

        $ownRequests = $ownRequestsQuery->paginate(10, ['*'], 'own_page');
        $allUsersRequests = $allUsersQuery->paginate(10, ['*'], 'all_page');


        $sisaCuti = (int) env('CUTI_TAHUNAN', 20)
            - (int) Leave::where('employee_id', Auth::id())
                ->where('status_1', 'approved')
                ->whereYear('date_start', now()->year)
                ->select(DB::raw('SUM(DATEDIFF(date_end, date_start) + 1) as total_days'))
                ->value('total_days');

        $totalRequests = Leave::count();
        $pendingRequests = Leave::where('status_1', 'pending')->count();
        $approvedRequests = Leave::where('status_1', 'approved')->count();
        $rejectedRequests = Leave::where('status_1', 'rejected')->count();

        $manager = User::where('role', Roles::Manager->value)->first();

        Leave::whereNull('seen_by_manager_at')
            // ->whereHas('employee', fn($q) => $q->where('division_id', auth()->user()->division_id))
            ->update(['seen_by_manager_at' => now()]);

        return view('manager.leave-request.index', compact(
            'ownRequests',
            'allUsersRequests',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'sisaCuti',
            'manager'
        ));
    }

    public function show(Leave $leave)
    {
        $leave->load(['employee', 'approver']);
        return view('manager.leave-request.show', compact('leave'));
    }
    public function create(LeaveService $leaveService)
    {
        $sisaCuti = $leaveService->sisaCuti(Auth::user());

        if ($sisaCuti <= 0) {
            abort(422, 'Sisa cuti tidak cukup.');
        }
        return view('manager.leave-request.create');
    }

    public function store(StoreLeaveRequest $request, LeaveService $leaveService)
    {
        $user = Auth::user();
        $sisaCuti = $leaveService->sisaCuti($user);
        $hariBaru = $leaveService->hitungHariCuti($request->date_start, $request->date_end);

        if ($hariBaru > $sisaCuti) {
            return back()->with('error', "Sisa cuti hanya {$sisaCuti} hari.");
        }

        $leaveService->createLeave($request->validated());

        return redirect()->route('manager.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function edit(Leave $leave)
    {
        return view('manager.leave-request.update', compact('leave'));
    }



    public function destroy(Leave $leave)
    {
        // Check if the user has permission to delete this leave
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow deleting if the leave is still pending
        if (($leave->status_1 !== 'pending')) {
            return redirect()->route('manager.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        $leave->delete();
        return redirect()->route('manager.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }


    public function update(ApproveLeaveRequest $request, Leave $leave)
    {
        if ($leave->status_1 !== 'pending') {
            return back()->with('error', 'Leave sudah diproses dan tidak dapat diubah lagi.');
        }

        $validated = $request->validated();

        $leave->update([
            'status_1' => $validated['status_1'],
            'note_1' => $validated['note_1'] ?? null,
        ]);

        return redirect()
            ->route('manager.leaves.index')
            ->with('success', "Leave request {$validated['status_1']} successfully.");
    }


    public function updateSelf(UpdateLeaveRequest $request, Leave $leave, LeaveService $leaveService)
    {
        if ($leave->status_1 !== 'pending') {
            return redirect()->route('manager.leaves.index', $leave->id)
                ->with('error', 'Cuti sudah diproses, tidak bisa diupdate.');
        }

        $newDays = $leaveService->hitungHariCuti($request->date_start, $request->date_end);
        $oldDays = $leaveService->hitungHariCuti($leave->date_start, $leave->date_end);
        $sisaCuti = $leaveService->sisaCuti(Auth::user(), $leave->id);

        if ($newDays > $oldDays && $sisaCuti < ($newDays - $oldDays)) {
            return back()->with('error', 'Sisa cuti tidak mencukupi untuk memperpanjang cuti.');
        }

        $leaveService->updateLeave($leave, $request->validated());

        return redirect()->route('manager.leaves.index', $leave->id)
            ->with('success', 'Leave request updated successfully.');
    }

    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Employee.leaves.pdf', compact('leave'));
        return $pdf->download('leave-details.pdf');
    }
}

