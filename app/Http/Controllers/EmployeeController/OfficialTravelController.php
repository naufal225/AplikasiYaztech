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

        // Query utama untuk list data (ada orderBy)
        $query = OfficialTravel::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        // Apply filters ke query utama
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

        // Data tabel (ada pagination)
        $officialTravels = $query->paginate(10)->withQueryString();

        // 🔹 Query baru khusus aggregate (tanpa orderBy)
        $countsQuery = OfficialTravel::where('employee_id', $user->id);

        if ($request->filled('status')) {
            // pake scope yang sama atau ulangi filter disini kalau perlu
            $status = $request->status;
            $countsQuery->where(function ($q) use ($status) {
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
            $countsQuery->where(
                'date_start',
                '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $countsQuery->where(
                'date_end',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        // 🔹 Jalankan aggregate aman
        $counts = $countsQuery->withFinalStatusCount()->first();

        $totalRequests    = (int) $countsQuery->count();
        $pendingRequests  = (int) ($counts->pending ?? 0);
        $approvedRequests = (int) ($counts->approved ?? 0);
        $rejectedRequests = (int) ($counts->rejected ?? 0);

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.travels.travel-show', compact(
            'officialTravels',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'manager'
        ));
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

        $start = \Carbon\Carbon::parse($validated['date_start'])->startOfDay();
        $end = \Carbon\Carbon::parse($validated['date_end'])->startOfDay();

        $totalDays = $start->diffInDays($end) + 1;

        $user = Auth::user();
        $userName = $user->name;
        $userEmail = $user->email;
        $divisionId = $user->division_id;

        // Hitung biaya per hari
        $weekDayCost = (int) env('TRAVEL_COSTS_WEEK_DAY', 0);
        $weekEndCost = (int) env('TRAVEL_COSTS_WEEK_END', 0);

        // Ambil semua holiday dari DB
        $holidayDates = \App\Models\Holiday::pluck('holiday_date')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->toArray();

        $period = \Carbon\CarbonPeriod::create($start, $end);

        $totalCost = 0;
        foreach ($period as $date) {
            $isWeekend = $date->isWeekend();
            $isHoliday = in_array($date->toDateString(), $holidayDates);

            if ($isWeekend || $isHoliday) {
                $totalCost += $weekEndCost;
            } else {
                $totalCost += $weekDayCost;
            }
        }

        DB::transaction(function () use ($request, $start, $end, $totalDays, $user, $userName, $userEmail, $divisionId, $totalCost) {
            $officialTravel = new OfficialTravel();
            $officialTravel->customer = $request->customer;
            $officialTravel->employee_id = Auth::id();
            $officialTravel->date_start = $start;
            $officialTravel->date_end = $end;
            $officialTravel->total = $totalCost;
            $officialTravel->status_1 = 'pending';
            $officialTravel->status_2 = 'pending';
            $officialTravel->save();

            $tokenRaw = null;
            if ($officialTravel->approver) {
                $tokenRaw = Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($officialTravel),
                    'model_id' => $officialTravel->id,
                    'approver_user_id' => $officialTravel->approver->id,
                    'level' => 1,
                    'scope' => 'both',
                    'token' => hash('sha256', $tokenRaw),
                    'expires_at' => now()->addDays(3),
                ]);
            }

            DB::afterCommit(function () use ($officialTravel, $tokenRaw, $totalDays, $userName, $userEmail, $divisionId) {
                $fresh = $officialTravel->fresh();

                event(new \App\Events\OfficialTravelSubmitted($fresh, $divisionId));

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
        if ($user->id !== (int) $officialTravel->employee_id) {
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
        if ($user->id !== (int) $officialTravel->employee_id) {
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
        if ($user->id !== (int) $officialTravel->employee_id) {
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

        $totalDays = $start->diffInDays($end) + 1;

        // Hitung biaya per hari
        $weekDayCost = (int) env('TRAVEL_COSTS_WEEK_DAY', 0);
        $weekEndCost = (int) env('TRAVEL_COSTS_WEEK_END', 0);

        // Ambil semua holiday dari DB
        $holidayDates = \App\Models\Holiday::pluck('holiday_date')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->toArray();

        $period = \Carbon\CarbonPeriod::create($start, $end);

        $totalCost = 0;
        foreach ($period as $date) {
            $isWeekend = $date->isWeekend();
            $isHoliday = in_array($date->toDateString(), $holidayDates);

            if ($isWeekend || $isHoliday) {
                $totalCost += $weekEndCost;
            } else {
                $totalCost += $weekDayCost;
            }
        }

        $officialTravel->customer = $request->customer;
        $officialTravel->date_start = $request->date_start;
        $officialTravel->date_end = $request->date_end;
        $officialTravel->status_1 = 'pending';
        $officialTravel->status_2 = 'pending';
        $officialTravel->note_1 = NULL;
        $officialTravel->note_2 = NULL;
        $officialTravel->total = $totalCost;
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

        if (\App\Models\ApprovalLink::where('model_id', $officialTravel->id)->where('model_type', get_class($officialTravel))->exists()) {
            \App\Models\ApprovalLink::where('model_id', $officialTravel->id)->where('model_type', get_class($officialTravel))->delete();
        }

        $officialTravel->delete();

        return redirect()->route('employee.official-travels.index')
            ->with('success', 'Official travel request deleted successfully.');
    }
}
