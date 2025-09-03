<?php

namespace App\Http\Controllers\EmployeeController;

use App\Events\LeaveSubmitted;
use App\Roles;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave;
use App\Models\User;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $queryClone = (clone $query);

        if ($request->filled('status')) {
            $status = $request->status;

            $query->where(function ($q) use ($status) {
                if ($status === 'rejected') {
                    $q->where('status_1', 'rejected');
                } elseif ($status === 'approved') {
                    $q->where('status_1', 'approved');
                } elseif ($status === 'pending') {
                    $q->where(function ($sub) {
                        $sub->where('status_1', 'pending');
                    })
                        ->where('status_1', '!=', 'rejected')
                        ->where(function ($sub) {
                            $sub->where('status_1', '!=', 'approved');
                        });
                }
            });
        }

        if ($request->filled('from_date')) {
            $query->where(
                'date_start',
                '>=',
                Carbon::parse($request->from_date)
                    ->startOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where(
                'date_start',
                '<=',
                Carbon::parse($request->to_date)
                    ->endOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        $leaves = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) $queryClone->where('status_1', 'approved')->whereYear('date_start', now()->year)->count();
        $totalRequests = (int) $queryClone->count();
        $pendingRequests = (int) $counts->pending;
        $approvedRequests = (int) $counts->approved;
        $rejectedRequests = (int) $counts->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.leaves.leave-show', compact('leaves', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager', 'sisaCuti'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) Leave::where('employee_id', Auth::id())->with(['employee', 'approver'])->orderBy('created_at', 'desc')->where('status_1', 'approved')->whereYear('date_start', now()->year)->count();

        if ($sisaCuti <= 0) {
            abort(422, 'Sisa cuti tidak cukup.');
        }

        return view('Employee.leaves.leave-request');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        ]);

        if (!Auth::user()->division_id) {
            return back()->with('error', 'You are not in a division. Please contact your administrator.');
        }

        DB::transaction(function () use ($request) {
            $leave = new Leave();
            $leave->employee_id = Auth::id();
            $leave->date_start = $request->date_start;
            $leave->date_end = $request->date_end;
            $leave->reason = $request->reason;
            $leave->status_1 = 'pending';
            $leave->save();

            $tokenRaw = null;
            $manager = User::where('role', Roles::Manager->value)->first();
            if ($manager) {
                $token = Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($leave),   // App\Models\Leave
                    'model_id' => $leave->id,
                    'approver_user_id' => $manager->id,
                    'level' => 2,
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $token), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);
            }

            // pastikan broadcast SETELAH commit
            DB::afterCommit(function () use ($leave, $request, $tokenRaw, $manager) {
                $fresh = $leave->fresh(); // ambil ulang (punya created_at dll)

                event(new \App\Events\LeaveLevelAdvanced($fresh, Auth::user()->division_id, 'manager'));

                if (!$fresh || !$fresh->approver || !$tokenRaw) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $tokenRaw); // pastikan route param sesuai

                // Gunakan queue
                Mail::to($manager->email)->queue(
                    new \App\Mail\SendMessage(
                        namaPengaju: $leave->employee->name,
                        namaApprover: $manager->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: $leave->employee->email
                    )
                );
            });

        });

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
        if ($user->id !== $leave->employee_id && ($user->role == Roles::Admin->value || $user->role == Roles::Manager->value || $user->role == Roles::Approver->value)) {
            abort(403, 'Unauthorized action.');
        }

        $leave->load(['employee', 'approver']);
        return view('Employee.leaves.leave-detail', compact('leave'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Employee.leaves.pdf', compact('leave'));
        return $pdf->download('leave-details.pdf');
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
        if ($leave->status_1 !== 'pending') {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }

        return view('Employee.leaves.leave-edit', compact('leave'));
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
        if ($leave->status_1 !== 'pending') {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot update a leave request that has already been processed.');
        }

        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'reason.string' => 'Alasan harus berupa teks.',
            'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        ]);

        $leave->date_start = $request->date_start;
        $leave->date_end = $request->date_end;
        $leave->reason = $request->reason;
        $leave->status_1 = 'pending';
        $leave->note_1 = NULL;
        $leave->save();

        // Send notification email to the approver
        $manager = User::where('role', Roles::Manager->value)->first();
        if ($manager) {
            $token = Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($leave),   // App\Models\Leave
                'model_id' => $leave->id,
                'approver_user_id' => $manager->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            Mail::to($manager->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $manager->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email
                )
            );
        }

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
        if (($leave->status_1 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        if (\App\Models\ApprovalLink::where('model_id', $leave->id)->where('model_type', get_class($leave))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $leave->id)->where('model_type', get_class($leave))->delete();
        }

        $leave->delete();
        
        return redirect()->route('employee.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
