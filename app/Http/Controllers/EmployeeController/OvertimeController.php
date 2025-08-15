<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Roles;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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
            $query->where('status_1', $request->status)
            ->orWhere('status_2', $request->status);
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
        $pendingRequests = Overtime::where('employee_id', $user->id)->where('status_1', 'pending')->orWhere('status_2', 'pending')->count();
        $approvedRequests = Overtime::where('employee_id', $user->id)->where('status_1', 'approved')->orWhere('status_2', 'approved')->count();
        $rejectedRequests = Overtime::where('employee_id', $user->id)->where('status_1', 'rejected')->orWhere('status_2', 'rejected')->count();

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
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end' => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ]);

        // Parsing waktu input
        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end   = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        // Hitung langsung dari date_start
        $overtimeMinutes = $start->diffInMinutes($end);
        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        $overtime = Overtime::create([
            'employee_id' => Auth::id(),
            'date_start'  => $start,
            'date_end'    => $end,
            'total'       => $overtimeMinutes, // Simpan dalam menit
            'status_1'    => 'pending',
            'status_2'    => 'pending',
        ]);

        // Send notification email to the approver
        if ($overtime->approver) {
            $linkTanggapan = route('employee.overtimes.show', $overtime->id);

            $hours = floor($overtimeMinutes / 60);
            $minutes = $overtimeMinutes % 60;
            $pesan = "Pengajuan lembur milik " . Auth::user()->name . " telah dilakukan perubahan data.
                <br> Tanggal/Waktu Mulai: " . $request->date_start . "
                <br> Tanggal/Waktu Akhir: " . $request->date_end . "
                <br> Total Waktu: " . $hours . " hours " . $minutes . " minutes";

            Mail::to($overtime->approver->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    pesan: $pesan,
                    namaApprover: $overtime->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                )
            );
        }

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime submitted. Total: '. $hours . ' hours ' . $minutes . ' minutes');
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
            'date_start' => 'required|date_format:Y-m-d\TH:i',
            'date_end'   => 'required|date_format:Y-m-d\TH:i|after:date_start',
        ]);

        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_start, 'Asia/Jakarta');
        $end   = Carbon::createFromFormat('Y-m-d\TH:i', $request->date_end, 'Asia/Jakarta');

        $overtimeMinutes = $start->diffInMinutes($end);

        $overtimeHours = $overtimeMinutes / 60;

        if ($overtimeHours < 0.5) {
            return back()->withErrors(['date_end' => 'Minimum overtime is 0.5 hours. Please adjust your end time.']);
        }

        // Simpan data
        $overtime->date_start = $request->date_start;
        $overtime->date_end   = $request->date_end;
        $overtime->total      = $overtimeMinutes; // Disimpan dalam menit
        $overtime->status_1   = 'pending';
        $overtime->status_2   = 'pending';
        $overtime->save();

        // Send notification email to the approver
        if ($overtime->approver) {
            $linkTanggapan = route('employee.overtimes.show', $overtime->id);

            $hours = floor($overtimeMinutes / 60);
            $minutes = $overtimeMinutes % 60;
            $pesan = "Pengajuan lembur milik " . Auth::user()->name . " telah dilakukan perubahan data.
                <br> Tanggal/Waktu Mulai: " . $request->date_start . "
                <br> Tanggal/Waktu Akhir: " . $request->date_end . "
                <br> Total Waktu: " . $hours . " hours " . $minutes . " minutes";

            Mail::to($overtime->approver->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    pesan: $pesan,
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

        $overtime->delete();

        return redirect()->route('employee.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
    }
}