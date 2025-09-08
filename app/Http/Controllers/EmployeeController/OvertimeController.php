<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Roles;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Str;

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
        $queryClone = (clone $query);

        if ($request->filled('status')) {
            $status = $request->status;

            $query->where(function ($q) use ($status) {
                if ($status === 'rejected') {
                    $q->where('status_1', 'rejected')
                        ->orWhere('status_2', 'rejected');
                } elseif ($status === 'approved') {
                    $q->where('status_1', 'approved')
                        ->where('status_2', 'approved');
                } elseif ($status === 'pending') {
                    $q->where(function ($sub) {
                        $sub->where('status_1', 'pending')
                            ->orWhere('status_2', 'pending');
                    })
                        ->where('status_1', '!=', 'rejected')
                        ->where('status_2', '!=', 'rejected')
                        ->where(function ($sub) {
                            $sub->where('status_1', '!=', 'approved')
                                ->orWhere('status_2', '!=', 'approved');
                        });
                }
            });
        }

        if ($request->filled('from_date')) {
            $query->where(
                'date_start',
                '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where(
                'date_end',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $overtimes = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryClone->count();
        $pendingRequests = (int) $counts->pending;
        $approvedRequests = (int) $counts->approved;
        $rejectedRequests = (int) $counts->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.overtimes.overtime-show', compact('overtimes', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
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
            'customer' => 'required',
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ], [
            'customer.required' => 'Customer harus diisi.',
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
        ]);

        // Parsing waktu input
        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        // Hitung langsung dari date_start
        $overtimeMinutes = $start->diffInMinutes($end);
        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        $hours = round($overtimeMinutes / 60);
        $minutes = $overtimeMinutes % 60;

        DB::transaction(function () use ($start, $end, $overtimeMinutes, $hours, $minutes, $request) {
            $overtime = new Overtime();
            $overtime->employee_id = Auth::id();
            $overtime->customer = $request->customer;
            $overtime->date_start = $start;
            $overtime->date_end = $end;
            $overtime->total = (int) ((int) ($hours * (int) env('OVERTIME_COSTS', 0)) + (int) env('MEAL_COSTS', 0));
            $overtime->status_1 = 'pending';
            $overtime->status_2 = 'pending';
            $overtime->save();

            $token = null;
            // Send notification email to the approver
            if ($overtime->approver) {
                $token = \Illuminate\Support\Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($overtime),   // App\Models\overtime
                    'model_id' => $overtime->id,
                    'approver_user_id' => $overtime->approver->id,
                    'level' => 1, // level 1 berarti arahnya ke team lead
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $token), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);

            }

            DB::afterCommit(function () use ($overtime, $token, $start, $end, $hours, $minutes) {
                $fresh = $overtime->fresh(); // ambil ulang (punya created_at dll)
                // dd("jalan");
                event(new \App\Events\OvertimeSubmitted($fresh, Auth::user()->division_id));

                // Kalau tidak ada approver atau token, jangan kirim email
                
                if (!$fresh || !$fresh->approver || !$token) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $token);

                Mail::to($overtime->approver->email)->send(
                    new \App\Mail\SendMessage(
                        namaPengaju: Auth::user()->name,
                        namaApprover: $overtime->approver->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: Auth::user()->email,
                    )
                );
            });

        });

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime submitted. Total: ' . $hours . ' hours ' . $minutes . ' minutes');
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
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Overtime $overtime)
    {
        $pdf = Pdf::loadView('Employee.overtimes.pdf', compact('overtime'));
        return $pdf->download('overtime-details.pdf');
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

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
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

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot update an overtime request that has already been processed.');
        }

        $request->validate([
            'customer' => 'required',
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
        ]);

        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        $overtimeMinutes = $start->diffInMinutes($end);

        $overtimeHours = round($overtimeMinutes / 60);

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        // Simpan data
        $overtime->customer = $request->customer;
        $overtime->date_start = $request->date_start;
        $overtime->date_end = $request->date_end;
        $overtime->total = (int) ($overtimeHours * (int) env('OVERTIME_COSTS', 0)) + (int) env('MEAL_COSTS', 0);
        $overtime->status_1 = 'pending';
        $overtime->status_2 = 'pending';
        $overtime->note_1 = NULL;
        $overtime->note_2 = NULL;
        $overtime->save();

        // Send notification email to the approver
        if ($overtime->approver) {
            $token = \Illuminate\Support\Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($overtime),   // App\Models\overtime
                'model_id' => $overtime->id,
                'approver_user_id' => $overtime->approver->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            $hours = floor($overtimeMinutes / 60);
            $minutes = $overtimeMinutes % 60;

            Mail::to($overtime->approver->email)->queue(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $overtime->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                )
            );
        }

        return redirect()->route('employee.overtimes.show', $overtime->id)
            ->with('success', 'Overtime request updated successfully. Total overtime: ' . $hours . ' hours ' . $minutes . ' minutes');
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

        if (($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.overtimes.show', $overtime->id)
                ->with('error', 'You cannot delete an overtime request that has already been processed.');
        }

        if (\App\Models\ApprovalLink::where('model_id', $overtime->id)->where('model_type', get_class($overtime))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $overtime->id)->where('model_type', get_class($overtime))->delete();
        }

        $overtime->delete();

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
    }
}
