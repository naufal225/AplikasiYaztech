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

        // Query utama untuk list data
        $query = Leave::where('employee_id', $user->id)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter status
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

        // Filter tanggal
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

        // Data untuk tabel (pagination)
        $leaves = $query->paginate(10)->withQueryString();

        // 🔹 Query baru khusus untuk aggregate (tanpa orderBy)
        $countsQuery = Leave::where('employee_id', $user->id);
        if ($request->filled('status')) {
            $countsQuery->filterFinalStatus($request->status); // pakai scope dari HasDualStatus
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

        $counts = $countsQuery->withFinalStatusCount()->first();

        // Hitung total cuti
        $tahunSekarang = now()->year;
        $totalHariCuti = Leave::where('employee_id', $user->id)
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang) {
                $start = Carbon::parse($cuti->date_start);
                $end   = Carbon::parse($cuti->date_end);

                if ($start->year < $tahunSekarang) {
                    $start = Carbon::create($tahunSekarang, 1, 1);
                }
                if ($end->year > $tahunSekarang) {
                    $end = Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
            });

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - $totalHariCuti;

        // 🔹 Ambil count aman
        $totalRequests     = (int) Leave::where('employee_id', $user->id)->count();
        $pendingRequests   = (int) ($counts->pending ?? 0);
        $approvedRequests  = (int) ($counts->approved ?? 0);
        $rejectedRequests  = (int) ($counts->rejected ?? 0);

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.leaves.leave-show', compact(
            'leaves',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'manager',
            'sisaCuti'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tahunSekarang = now()->year;

        $totalHariCuti = (int) Leave::where('employee_id', Auth::id())
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang) {
                $start = \Carbon\Carbon::parse($cuti->date_start);
                $end   = \Carbon\Carbon::parse($cuti->date_end);

                // Batasi tanggal ke dalam tahun berjalan
                if ($start->year < $tahunSekarang) {
                    $start = \Carbon\Carbon::create($tahunSekarang, 1, 1);
                }
                if ($end->year > $tahunSekarang) {
                    $end = \Carbon\Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
            });

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - $totalHariCuti;

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

        $tahunSekarang = now()->year;

        // Hitung cuti yang sudah terpakai
        $totalHariCuti = (int) Leave::where('employee_id', Auth::id())
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang) {
                $start = \Carbon\Carbon::parse($cuti->date_start);
                $end   = \Carbon\Carbon::parse($cuti->date_end);

                if ($start->year < $tahunSekarang) {
                    $start = \Carbon\Carbon::create($tahunSekarang, 1, 1);
                }
                if ($end->year > $tahunSekarang) {
                    $end = \Carbon\Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
            });

        // Hitung cuti yang sedang diajukan
        $startBaru = \Carbon\Carbon::parse($request->date_start);
        $endBaru   = \Carbon\Carbon::parse($request->date_end);
        $hariCutiBaru = $startBaru->diffInDays($endBaru) + 1;

        $jatahTahunan = (int) env('CUTI_TAHUNAN', 20);
        $sisaCuti = $jatahTahunan - $totalHariCuti;

        if ($hariCutiBaru > $sisaCuti) {
            return back()->with('error', "Sisa cuti hanya {$sisaCuti} hari, tidak bisa ajukan {$hariCutiBaru} hari.");
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
        $user = Auth::user();
        if ($user->id !== (int) $leave->employee_id) {
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
        $user = Auth::user();
        if ($user->id !== $leave->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($leave->status_1 !== 'pending') {
            return redirect()->route('employee.leaves.show', $leave->id)
                ->with('error', 'You cannot update a leave request that has already been processed.');
        }

        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'required|string|max:1000',
        ]);

        // --- Hitung lama cuti baru
        $newStart = \Carbon\Carbon::parse($request->date_start);
        $newEnd   = \Carbon\Carbon::parse($request->date_end);
        $newDays  = $newStart->diffInDays($newEnd) + 1;

        // --- Hitung lama cuti lama (sebelum update)
        $oldStart = \Carbon\Carbon::parse($leave->date_start);
        $oldEnd   = \Carbon\Carbon::parse($leave->date_end);
        $oldDays  = $oldStart->diffInDays($oldEnd) + 1;

        // --- Ambil sisa cuti saat ini (tanpa cuti yang sedang diedit)
        $tahunSekarang = now()->year;
        $totalHariCuti = (int) Leave::where('employee_id', $user->id)
            ->where('id', '!=', $leave->id) // exclude cuti yg sedang diupdate
            ->where('status_1', 'approved')
            ->where(function ($q) use ($tahunSekarang) {
                $q->whereYear('date_start', $tahunSekarang)
                ->orWhereYear('date_end', $tahunSekarang);
            })
            ->get()
            ->sum(function ($cuti) use ($tahunSekarang) {
                $start = \Carbon\Carbon::parse($cuti->date_start);
                $end   = \Carbon\Carbon::parse($cuti->date_end);

                if ($start->year < $tahunSekarang) {
                    $start = \Carbon\Carbon::create($tahunSekarang, 1, 1);
                }
                if ($end->year > $tahunSekarang) {
                    $end = \Carbon\Carbon::create($tahunSekarang, 12, 31);
                }

                return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
            });

        $jatahCuti = (int) env('CUTI_TAHUNAN', 20);
        $sisaCuti  = $jatahCuti - $totalHariCuti;

        // --- Kalau cuti baru lebih panjang dari sebelumnya, cek dulu sisa cuti
        if ($newDays > $oldDays) {
            $butuhTambahan = $newDays - $oldDays;
            if ($sisaCuti < $butuhTambahan) {
                return back()->with('error', 'Sisa cuti tidak mencukupi untuk memperpanjang cuti.');
            }
        }

        // --- Update data cuti
        $leave->date_start = $request->date_start;
        $leave->date_end   = $request->date_end;
        $leave->reason     = $request->reason;
        $leave->status_1   = 'pending';
        $leave->note_1     = NULL;
        $leave->save();

        // --- Kirim notifikasi ke manager
        $manager = User::where('role', Roles::Manager->value)->first();
        if ($manager) {
            $token = Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($leave),
                'model_id' => $leave->id,
                'approver_user_id' => $manager->id,
                'level' => 1,
                'scope' => 'both',
                'token' => hash('sha256', $token),
                'expires_at' => now()->addDays(3),
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            Mail::to($manager->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: $user->name,
                    namaApprover: $manager->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: $user->email
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
