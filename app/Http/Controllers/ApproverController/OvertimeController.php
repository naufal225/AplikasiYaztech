<?php

namespace App\Http\Controllers\ApproverController;

use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            if ($validated['status_1'] === 'rejected' && $overtime->status_1 === 'pending') {
                $overtime->update([
                    'status_1' => 'rejected',
                    'note_1' => $validated['note_1'] ?? "",
                    'status_2' => 'rejected', // ikut rejected juga
                    'note_2' => $validated['note_2'] ?? "",
                ]);
            } else {
                $overtime->update([
                    'status_1' => $validated['status_1'],
                    'note_1' => $validated['note_1'] ?? ""
                ]);
            }

            $status = $validated['status_1'];
        } else if ($request->has('status_2')) {
            $overtime->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? ""
            ]);
            $status = $validated['status_2'];
        }

        return redirect()->route('approver.overtimes.index')->with('success', 'Overtime request ' . $status . ' successfully.');
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
