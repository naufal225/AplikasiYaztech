<?php

namespace App\Http\Controllers\ApproverController;

use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $query = Reimbursement::query()
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

        $reimbursements = $query->paginate(10);

        // Total (sesuai akses approver/leader, bukan semua tabel)
        $baseForCounts = Reimbursement::forLeader(Auth::id());

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

        return view('approver.reimbursement.index', compact(
            'reimbursements',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests'
        ));
    }

    public function show(Reimbursement $reimbursement)
    {
        if ($reimbursement->approver->id !== Auth::id()) {
            return abort(403, 'Unauthorized');
        }

        $reimbursement->load(['employee', 'approver']);
        return view('approver.reimbursement.show', compact('reimbursement'));
    }

    public function update(Request $request, Reimbursement $reimbursement)
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

            if ($reimbursement->status_1 !== 'pending') {
                return back()->withErrors(['status_1' => 'Status 1 sudah final dan tidak dapat diubah.']);
            }

            // Jika direject, cascade ke status_2 juga
            if ($validated['status_1'] === 'rejected') {
                $reimbursement->update([
                    'status_1' => 'rejected',
                    'note_1' => $validated['note_1'] ?? '',
                    'status_2' => 'rejected',
                    'note_2' => $validated['note_2'] ?? '',
                ]);
            } else {
                // approved → kirim notifikasi ke manager
                $reimbursement->update([
                    'status_1' => 'approved',
                    'note_1' => $validated['note_1'] ?? '',
                ]);

                $manager = User::where('role', Roles::Manager->value)->first();
                if ($manager) {
                    $link = route('manager.reimbursements.show', $reimbursement->id);
                    $pesan = "Terdapat pengajuan cuti baru atas nama {$reimbursement->employee->name}.
                          <br> Tanggal Mulai: {$reimbursement->date_start}
                          <br> Tanggal Selesai: {$reimbursement->date_end}
                          <br> Alasan: {$reimbursement->reason}";

                    // Gunakan queue
                    Mail::to($manager->email)->queue(
                        new \App\Mail\SendMessage(
                            namaPengaju: $reimbursement->employee->name,
                            pesan: $pesan,
                            namaApprover: $manager->name,
                            linkTanggapan: $link,
                            emailPengaju: $reimbursement->employee->email
                        )
                    );
                }
            }

            $statusMessage = $validated['status_1'];
        }

        // === STATUS 2 ===
        elseif ($request->filled('status_2')) {
            if ($reimbursement->status_1 !== 'approved') {
                return back()->withErrors(['status_2' => 'Status 2 hanya dapat diubah setelah status 1 disetujui.']);
            }

            if ($reimbursement->status_2 !== 'pending') {
                return back()->withErrors(['status_2' => 'Status 2 sudah final dan tidak dapat diubah.']);
            }

            $reimbursement->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? ''
            ]);

            $statusMessage = $validated['status_2'];
        }

        return redirect()
            ->route('approver.reimbursements.index')
            ->with('success', "Reimbursement request {$statusMessage} successfully.");
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

            $filename = 'reimbursement-requests-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

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
