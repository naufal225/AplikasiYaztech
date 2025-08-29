<?php

namespace App\Http\Controllers\FinanceController;

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
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        // --- Query untuk "Your Leaves"
        $yourLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $yourLeavesQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $yourLeavesQuery->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('status')) {
            $yourLeavesQuery->where('status_1', $request->status);
        }

        $yourLeaves = $yourLeavesQuery->paginate(10, ['*'], 'your_page');


        // --- Query untuk "All Leaves"
        $allLeavesQuery = Leave::with(['employee', 'approver'])
            ->where('status_1', 'approved')
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $allLeavesQuery->where('date_start', '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $allLeavesQuery->where('date_start', '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $allLeaves = $allLeavesQuery->paginate(10, ['*'], 'all_page');

        $counts = (clone $allLeavesQuery)->withFinalStatusCount()->first();
        $totalRequests = Leave::count();
        $approvedRequests = (int) $counts->approved;

        $countsYours = (clone $yourLeavesQuery)->withFinalStatusCount()->first();

        $sisaCuti = (int) env('CUTI_TAHUNAN', 20) - (int) Leave::where('employee_id', Auth::id())
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')->where('status_1', 'approved')->whereYear('date_start', now()->year)->count();
        $totalYoursRequests = (int) $yourLeavesQuery->count();
        $pendingYoursRequests = (int) $countsYours->pending;
        $approvedYoursRequests = (int) $countsYours->approved;
        $rejectedYoursRequests = (int) $countsYours->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Finance.leaves.leave-show', compact(
            'yourLeaves',
            'allLeaves',
            'totalRequests',
            'approvedRequests',
            'manager',
            'sisaCuti',
            'totalYoursRequests',
            'pendingYoursRequests',
            'approvedYoursRequests',
            'rejectedYoursRequests'
        ));
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

        return view('Finance.leaves.leave-request');
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

        return redirect()->route('finance.leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        $leave->load(['employee', 'approver']);
        return view('Finance.leaves.leave-detail', compact('leave'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Leave $leave)
    {
        $pdf = Pdf::loadView('Finance.leaves.pdf', compact('leave'));
        return $pdf->download('leave-details-finance.pdf');
    }

    /**
     * Bulk export approved requests as PDFs in a ZIP file.
     */
    public function bulkExport(Request $request)
    {
        $dateFrom = $request->input('from_date');
        $dateTo = $request->input('date_to');

        $query = Leave::with('employee')->where('status_1', 'approved');

        if ($dateFrom && $dateTo) {
            $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('date_start', '<=', $dateTo)
                ->whereDate('date_end', '>=', $dateFrom);
            });
        }

        $leaves = $query->get();

        if ($leaves->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk filter tersebut.');
        }

        $zipFileName = 'LeaveRequests_' . Carbon::now()->format('YmdHis') . '.zip';
        $zipPath = Storage::disk('public')->path($zipFileName);

        // Folder sementara untuk menyimpan PDF
        $tempFolder = 'temp_leaves';
        if (!Storage::disk('public')->exists($tempFolder)) {
            Storage::disk('public')->makeDirectory($tempFolder);
        }

        $files = [];

        foreach ($leaves as $leave) {
            $pdf = Pdf::loadView('Finance.leaves.pdf', compact('leave'));
            $fileName = "leave_{$leave->employee->name}_" . $leave->id . ".pdf";
            $filePath = "{$tempFolder}/{$fileName}";
            Storage::disk('public')->put($filePath, $pdf->output());
            $files[] = Storage::disk('public')->path($filePath);
        }

        // Buat ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Bersihkan file sementara
        foreach ($files as $file) {
            @unlink($file);
        }
        Storage::disk('public')->deleteDirectory($tempFolder);

        // Return download
        return response()->download($zipPath)->deleteFileAfterSend(true);
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
            return redirect()->route('finance.leaves.show', $leave->id)
                ->with('error', 'You cannot edit a leave request that has already been processed.');
        }

        return view('Finance.leaves.leave-edit', compact('leave'));
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
            return redirect()->route('finance.leaves.show', $leave->id)
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

        return redirect()->route('finance.leaves.show', $leave->id)
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
            return redirect()->route('finance.leaves.show', $leave->id)
                ->with('error', 'You cannot delete a leave request that has already been processed.');
        }

        $leave->delete();
        return redirect()->route('finance.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
