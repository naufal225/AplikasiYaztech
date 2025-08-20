<?php

namespace App\Http\Controllers\ApproverController;

use App\Events\OvertimeLevelAdvanced;
use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\Overtime;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $query = Overtime::query()
            ->forLeader(Auth::id())
            ->with(['employee', 'approver'])
            ->orderByDesc('created_at')
            ->filterFinalStatus($request->input('status'));

        // Filter tanggal (opsional)
        if ($request->filled('from_date')) {
            $from = Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta');
            $query->where('date_start', '>=', $from);
        }
        if ($request->filled('to_date')) {
            $to = Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta');
            $query->where('date_start', '<=', $to);
        }

        $overtimes = $query->paginate(10);

        // Total (sesuai akses approver/leader, bukan semua tabel)
        $baseForCounts = Overtime::forLeader(Auth::id());

        // Terapkan filter tanggal yg sama ke agregasi
        if (isset($from))
            $baseForCounts->where('date_start', '>=', $from);
        if (isset($to))
            $baseForCounts->where('date_start', '<=', $to);

        // Satu query untuk 3 angka
        $counts = (clone $baseForCounts)->withFinalStatusCount()->first();

        $totalRequests = (clone $baseForCounts)->count();
        $approvedRequests = (int) ($counts->approved ?? 0);
        $rejectedRequests = (int) ($counts->rejected ?? 0);
        $pendingRequests = (int) ($counts->pending ?? 0);

         Overtime::whereNull('seen_by_approver_at')->where('status_1', 'pending')
            ->whereHas('employee', fn($q) => $q->where('division_id', auth()->user()->division_id))
            ->update(['seen_by_approver_at' => now()]);


        return view('approver.overtime.index', compact(
            'overtimes',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests'
        ));
    }


    public function show(Overtime $overtime)
    {
        if ($overtime->employee->division->leader->id !== Auth::id()) {
            return abort(403, 'Unauthorized');
        }

        $overtime->load(['employee', 'approver']);
        return view('approver.overtime.show', compact('overtime'));
    }

    public function create()
    {

    }

    public function store(Request $request)
    {

    }

    public function edit(Overtime $overtime)
    {

    }

    public function update(Request $request, Overtime $overtime)
    {
        $validated = $request->validate([
            'status_1' => 'nullable|string|in:approved,rejected',
            'status_2' => 'nullable|string|in:approved,rejected',
            'note_1' => 'nullable|string',
            'note_2' => 'nullable|string',
        ]);

        // Cegah update dua status sekaligus
        if ($request->filled('status_1') && $request->filled('status_2')) {
            return back()->withErrors(['status' => 'Hanya boleh mengubah salah satu status dalam satu waktu.']);
        }

        $statusMessage = '';

        // === STATUS 1 ===
        if ($request->filled('status_1')) {

            if ($overtime->status_1 !== 'pending') {
                return back()->withErrors(['status_1' => 'Status 1 sudah final dan tidak dapat diubah.']);
            }

            // Jika direject, cascade ke status_2 juga
            if ($validated['status_1'] === 'rejected') {
                $overtime->update([
                    'status_1' => 'rejected',
                    'note_1' => $validated['note_1'] ?? NULL,
                    'status_2' => 'rejected', // ikut rejected juga
                    'note_2' => $validated['note_2'] ?? NULL,
                ]);
            } else {
                // approved → kirim notifikasi ke manager
                $overtime->update([
                    'status_1' => 'approved',
                    'note_1' => $validated['note_1'] ?? NULL,
                ]);

                event(new OvertimeLevelAdvanced($overtime, Auth::user()->division_id, 'manager'));

                $manager = User::where('role', Roles::Manager->value)->first();
                if ($manager) {
                    $token = Str::random(48);
                    ApprovalLink::create([
                        'model_type' => get_class($overtime),   // App\Models\Overtime
                        'model_id' => $overtime->id,
                        'approver_user_id' => $manager->id,
                        'level' => 2,
                        'scope' => 'both',             // boleh approve & reject
                        'token' => hash('sha256', $token), // simpan hash, kirim raw
                        'expires_at' => now()->addDays(3),  // masa berlaku
                    ]);
                    $link = route('public.approval.show', $token);
                    $pesan = "Terdapat pengajuan perjalanan dinas baru atas nama {$overtime->employee->name}.
                          <br> Tanggal Mulai: {$overtime->date_start}
                          <br> Tanggal Selesai: {$overtime->date_end}
                          <br> Alasan: {$overtime->reason}";

                    // Gunakan queue
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: $overtime->employee->name,
                            pesan: $pesan,
                            namaApprover: $manager->name,
                            linkTanggapan: $link,
                            emailPengaju: $overtime->employee->email
                        )
                    );
                }
            }

            $statusMessage = $validated['status_1'];
        }

        // === STATUS 2 ===
        elseif ($request->filled('status_2')) {
            if ($overtime->status_1 !== 'approved') {
                return back()->withErrors(['status_2' => 'Status 2 hanya dapat diubah setelah status 1 disetujui.']);
            }

            if ($overtime->status_2 !== 'pending') {
                return back()->withErrors(['status_2' => 'Status 2 sudah final dan tidak dapat diubah.']);
            }

            $overtime->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? ''
            ]);

            $statusMessage = $validated['status_2'];
        }

        return redirect()
            ->route('approver.overtimes.index')
            ->with('success', "Overtime request {$statusMessage} successfully.");
    }


    public function export(Request $request)
    {
        try {
            // (opsional) disable debugbar yang suka nyisipin output
            if (app()->bound('debugbar')) {
                app('debugbar')->disable();
            }

            // bersihkan buffer agar XLSX tidak ketimpa
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $filters = [
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
            ];

            $filename = 'overtime-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new OvertimesExport($filters), $filename);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Export error: ' . $e->getMessage());

            // Return JSON error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
