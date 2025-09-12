<?php

namespace App\Http\Controllers\ManagerController;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\OfficialTravel;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OfficialTravelController extends Controller
{
    public function index(Request $request)
    {
        // Query for user's own requests (all statuses)
        $ownRequestsQuery = OfficialTravel::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = OfficialTravel::with(['employee', 'approver'])
            ->where(function ($q) {
                $q->where('employee_id', '!=', Auth::id())
                    ->orWhere(function ($subQ) {
                        $subQ->where('employee_id', Auth::id())
                            ->where('status_2', 'approved');
                    });
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $statusFilter = function ($query) use ($request) {
                switch ($request->status) {
                    case 'approved':
                        // approved = dua-duanya approved
                        $query->where('status_1', 'approved')
                            ->where('status_2', 'approved');
                        break;

                    case 'rejected':
                        // rejected = salah satu rejected
                        $query->where(function ($q) {
                            $q->where('status_1', 'rejected')
                                ->orWhere('status_2', 'rejected');
                        });
                        break;

                    case 'pending':
                        // pending = tidak ada rejected DAN (minimal salah satu pending)
                        $query->where(function ($q) {
                            $q->where(function ($qq) {
                                $qq->where('status_1', 'pending')
                                    ->orWhere('status_2', 'pending');
                            })->where(function ($qq) {
                                $qq->where('status_1', '!=', 'rejected')
                                    ->where('status_2', '!=', 'rejected');
                            });
                        });
                        break;

                    default:
                        // nilai status tak dikenal: biarkan tanpa filter atau lempar 422
                        // optional: $query->whereRaw('1=0');
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

        $totalRequests = OfficialTravel::count();
        $pendingRequests = OfficialTravel::where('status_1', 'pending')
            ->orWhere('status_2', 'pending')->count();
        $approvedRequests = OfficialTravel::where('status_1', 'approved')
            ->where('status_2', 'approved')->count();
        $rejectedRequests = OfficialTravel::where('status_1', 'rejected')
            ->orWhere('status_2', 'rejected')->count();

        OfficialTravel::whereNull('seen_by_manager_at')
            // ->whereHas('employee', fn($q) => $q->where('division_id', auth()->user()->division_id))
            ->update(['seen_by_manager_at' => now()]);

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('manager.official-travel.index', compact('allUsersRequests', 'ownRequests', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }



    public function show(OfficialTravel $officialTravel)
    {
        $officialTravel->load(['employee', 'approver']);
        return view('manager.official-travel.show', compact('officialTravel'));
    }

    public function create()
    {
        return view('manager.official-travel.create');
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
            $officialTravel->total = (int) ((int) $totalDays * (int) env('TRAVEL_COSTS_PER_DAY', 0));
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

        return redirect()->route('manager.official-travels.index')
            ->with('success', 'Official travel request submitted successfully. Total days: ' . $totalDays);
    }

    public function edit(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending') {
            return redirect()->route('manager.official-travels.edit', $officialTravel->id)
                ->with('error', 'You cannot edit a travel request that has already been processed.');
        }

        $officialTravel->load(['employee', 'approver']);

        return view('manager.official-travel.update', compact('officialTravel'));
    }

    public function updateSelf(Request $request, OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending') {
            return redirect()->route('manager.official-travels.show', $officialTravel->id)
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
        $officialTravel->total = (int) ((int) $totalDays * (int) env('TRAVEL_COSTS_PER_DAY', 0));
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

        return redirect()->route('manager.official-travels.show', $officialTravel->id)
            ->with('success', 'Official travel request updated successfully. Total days: ' . $totalDays);
    }

    public function update(Request $request, OfficialTravel $officialTravel)
    {
        $validated = $request->validate([
            'status_1' => 'string|in:approved,rejected',
            'status_2' => 'string|in:approved,rejected',
            'note_1' => 'nullable|string',
            'note_2' => 'nullable|string',
        ], [
            'status_1.string' => 'Status must be a valid string.',
            'status_1.in' => 'Status must approved or rejected.',

            'status_2.string' => 'Status must be a valid string.',
            'status_2.in' => 'Status must approved or rejected.',

            'note_1.string' => 'Note must be a valid string.',
            'note_2.string' => 'Note must be a valid string.',
        ]);

        $status = '';

        if ($request->has('status_1')) {
            $officialTravel->update([
                'status_1' => $validated['status_1'],
                'note_1' => $validated['note_1'] ?? null
            ]);
            $status = $validated['status_1'];
        } else if ($request->has('status_2')) {
            $officialTravel->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? null
            ]);
            $status = $validated['status_2'];
        }

        return redirect()->route('manager.official-travels.index')->with('success', 'Official travel request ' . $status . ' successfully.');
    }

    public function destroy(OfficialTravel $officialTravel)
    {
        $user = Auth::user();
        if ($user->id !== $officialTravel->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if (($officialTravel->status_1 !== 'pending' || $officialTravel->status_2 !== 'pending')) {
            return redirect()->route('manager.official-travels.show', $officialTravel->id)
                ->with('error', 'You cannot delete a travel request that has already been processed.');
        }

        $officialTravel->delete();

        return redirect()->route('manager.official-travels.index')
            ->with('success', 'Official travel request deleted successfully.');
    }

}
