<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Roles;
use App\Models\OfficialTravel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficialTravelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = OfficialTravel::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');
        $queryClone = (clone $query);

        // Apply filters
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
                'date_start',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $officialTravels = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryClone->count();
        $pendingRequests = (int) $counts->pending;
        $approvedRequests = (int) $counts->approved;
        $rejectedRequests = (int) $counts->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.travels.travel-show', compact('officialTravels', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Employee.travels.travel-request', compact('approvers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required',
            'date_start' => 'required|date|after_or_equal:today',
            'date_end' => 'required|date|after_or_equal:date_start',
        ], [
            'customer.required' => 'Customer harus diisi.',
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_start.after' => 'Tanggal/Waktu Mulai harus setelah sekarang.',
            'date_start.after_or_equal' => 'Tanggal/Waktu Mulai harus hari ini atau setelahnya.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'date_end.after_or_equal' => 'Tanggal/Waktu Akhir harus hari ini atau setelahnya.',
        ]);

        $start = Carbon::parse($validated['date_start']);
        $end = Carbon::parse($validated['date_end']);

        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;

        $user = Auth::user();
        $userName = $user->name;
        $userEmail = $user->email;
        $divisionId = $user->division_id;

        DB::transaction(function () use ($request, $start, $end, $totalDays, $user, $userName, $userEmail, $divisionId) {
            $officialTravel = new OfficialTravel();
            $officialTravel->customer = $request->customer;
            $officialTravel->employee_id = Auth::id();
            $officialTravel->date_start = $start;
            $officialTravel->date_end = $end;
            $officialTravel->total = $totalDays;
            $officialTravel->status_1 = 'pending';
            $officialTravel->status_2 = 'pending';
            $officialTravel->save();


            // Siapkan token kalau ada approver
            $tokenRaw = null;
            // Send notification email to the approver
            if ($officialTravel->approver) {
                $tokenRaw = Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($officialTravel),   // App\Models\officialTravel
                    'model_id' => $officialTravel->id,
                    'approver_user_id' => $officialTravel->approver->id,
                    'level' => 1, // level 1 berarti arahnya ke team lead
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $tokenRaw), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);

            }

            DB::afterCommit(function () use ($officialTravel, $tokenRaw, $totalDays, $userName, $userEmail, $divisionId) {
                $fresh = $officialTravel->fresh(); // ambil ulang (punya created_at dll)

                event(new \App\Events\OfficialTravelSubmitted($fresh, $divisionId));

                // Kalau tidak ada approver atau token, jangan kirim email
                if (!$fresh || !$fresh->approver || !$tokenRaw) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $tokenRaw);

                Mail::to($officialTravel->approver->email)->queue(
                    new \App\Mail\SendMessage(
                        namaPengaju: $userName,
                        namaApprover: $officialTravel->approver->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: $userEmail,
                    )
                );
            });

        });

        return redirect()->route('employee.official-travels.index')
            ->with('success', 'Official travel request submitted successfully. Total days: ' . $totalDays);
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id && $user->id !== $officialTravel->approver_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        $officialTravel->load(['employee', 'approver']);
        return view('Employee.travels.travel-detail', compact('officialTravel'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(OfficialTravel $officialTravel)
    {
        $pdf = Pdf::loadView('Employee.travels.pdf', compact('officialTravel'));
        return $pdf->download('official-travel-details.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending') {
            return redirect()->route('employee.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot edit a travel request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('Employee.travels.travel-edit', compact('officialTravel', 'approvers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending') {
            return redirect()->route('employee.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot update a travel request that has already been processed.');
        }

        $request->validate([
            'customer' => 'required',
            'date_start' => 'required|date|after_or_equal:today',
            'date_end' => 'required|date|after_or_equal:date_start',
        ], [
            'date_start.required' => 'Tanggal/Waktu Mulai harus diisi.',
            'date_start.date_format' => 'Format Tanggal/Waktu Mulai tidak valid.',
            'date_start.after_or_equal' => 'Tanggal/Waktu Mulai harus hari ini atau setelahnya.',
            'date_end.required' => 'Tanggal/Waktu Akhir harus diisi.',
            'date_end.date_format' => 'Format Tanggal/Waktu Akhir tidak valid.',
            'date_end.after' => 'Tanggal/Waktu Akhir harus setelah Tanggal/Waktu Mulai.',
            'date_end.after_or_equal' => 'Tanggal/Waktu Akhir harus hari ini atau setelahnya.',
            'customer.required' => 'Customer harus diisi.',
        ]);

        // Calculate total days
        $start = Carbon::parse($request->date_start);
        $end = Carbon::parse($request->date_end);

        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;

        $officialTravel->customer = $request->customer;
        $officialTravel->date_start = $request->date_start;
        $officialTravel->date_end = $request->date_end;
        $officialTravel->status_1 = 'pending';
        $officialTravel->status_2 = 'pending';
        $officialTravel->note_1 = NULL;
        $officialTravel->note_2 = NULL;
        $officialTravel->total = $totalDays;
        $officialTravel->save();

        // Send notification email to the approver
        if ($officialTravel->approver) {
            $token = Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($officialTravel),   // App\Models\officialTravel
                'model_id' => $officialTravel->id,
                'approver_user_id' => $officialTravel->approver->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);

            $linkTanggapan = route('public.approval.show', $token);

            Mail::to($officialTravel->approver->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $officialTravel->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                )
            );
        }

        return redirect()->route('employee.official-travels.show', $officialTravel->id)
            ->with('success', 'Official travel request updated successfully. Total days: ' . $totalDays);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if (($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('employee.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot delete a travel request that has already been processed.');
        }

        $officialTravel->delete();

        return redirect()->route('employee.official-travels.index')
            ->with('success', 'Official travel request deleted successfully.');
    }
}
