<?php

namespace App\Http\Controllers\AdminController;

use App\Exports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\Overtime;
use App\Models\User;
use App\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        // Query for user's own requests (all statuses)
        $ownRequestsQuery = Overtime::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = Overtime::with(['employee', 'approver'])
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

        $totalRequests = Overtime::count();
        $pendingRequests = Overtime::where('status_1', 'pending')
            ->orWhere('status_2', 'pending')->count();
        $approvedRequests = Overtime::where('status_1', 'approved')
            ->where('status_2', 'approved')->count();
        $rejectedRequests = Overtime::where('status_1', 'rejected')
            ->orWhere('status_2', 'rejected')->count();

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('admin.overtime.index', compact('allUsersRequests', 'ownRequests', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }

    public function show($id)
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->load(['employee', 'approver']);
        return view('admin.overtime.show', compact('overtime'));
    }

    public function edit(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
            return redirect()->route('admin.overtimes.show', $overtime->id)
                ->with('error', 'You cannot edit an overtime request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        return view('admin.overtime.update', compact('overtime', 'approvers'));
    }

      public function update(Request $request, Overtime $overtime)
    {
        $user = Auth::user();

        if ($user->id !== $overtime->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') {
            return redirect()->route('admin.overtimes.show', $overtime->id)
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

        return redirect()->route('admin.overtimes.show', $overtime->id)
            ->with('success', 'Overtime request updated successfully. Total overtime: ' . $hours . ' hours ' . $minutes . ' minutes');
    }

    public function create()
    {
        return view('admin.overtime.create');
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

        return redirect()->route('admin.overtimes.index')
            ->with('success', 'Overtime submitted. Total: ' . $hours . ' hours ' . $minutes . ' minutes');
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

    public function destroy(Overtime $overtime)
    {
        $user = Auth::user();
        if ($user->id !== $overtime->employee_id && $user->role !== Roles::Admin->value) {
            abort(403, 'Unauthorized action.');
        }

        if (($overtime->status_1 !== 'pending' || $overtime->status_2 !== 'pending') && $user->role !== Roles::Admin->value) {
            return redirect()->route('admin.overtimes.show', $overtime->id)
                ->with('error', 'You cannot delete an overtime request that has already been processed.');
        }

        $overtime->delete();

        return redirect()->route('admin.overtimes.index')
            ->with('success', 'Overtime request deleted successfully.');
    }

    public function exportPdf(Overtime $overtime)
    {
        $pdf = Pdf::loadView('Employee.overtimes.pdf', compact('overtime'));
        return $pdf->download('overtime-details.pdf');
    }

    public function exportPdfAllData(Request $request)
    {
        try {
            // Authorization: Only Admin
            if (Auth::user()->role !== Roles::Admin->value) {
                abort(403, 'Unauthorized action.');
            }

            // (opsional) disable debugbar
            if (app()->bound('debugbar')) {
                app('debugbar')->disable();
            }

            // Bersihkan buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Bangun query dasar untuk semua overtime
            // Pastikan eager loading semua relasi yang dibutuhkan oleh view PDF
            $query = Overtime::with(['employee', 'approver', 'employee.division']);

            // Terapkan filter status
            if ($request->filled('status')) {
                $statusFilter = $request->status;
                switch ($statusFilter) {
                    case 'approved':
                        $query->where('status_1', 'approved')
                            ->where('status_2', 'approved');
                        break;
                    case 'rejected':
                        $query->where(function ($q) {
                            $q->where('status_1', 'rejected')
                                ->orWhere('status_2', 'rejected');
                        });
                        break;
                    case 'pending':
                        // Logika "pending" yang kompleks
                        $query->where(function ($q) {
                            // Kondisi 1: Minimal satu status adalah 'pending'
                            $q->where(function ($qq) {
                                $qq->where('status_1', 'pending')
                                    ->orWhere('status_2', 'pending');
                            });
                            // Kondisi 2: Tidak ada status yang 'rejected'
                            $q->where(function ($qq) {
                                $qq->where('status_1', '!=', 'rejected')
                                    ->where('status_2', '!=', 'rejected');
                            });
                        });
                        break;
                    // Tidak ada case default, jadi jika status tidak valid, tidak ada filter
                }
            }

            // Terapkan filter tanggal
            if ($request->filled('from_date')) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta');
                $query->where('created_at', '>=', $fromDate);
            }
            if ($request->filled('to_date')) {
                $toDate = Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta');
                $query->where('created_at', '<=', $toDate);
            }

            // Ambil data
            $overtimes = $query->get();

            if ($overtimes->isEmpty()) {
                return response()->json(['message' => 'No data found for the selected filters.'], 404);
            }

            // Buat direktori sementara untuk menyimpan PDF
            // uniqid() memastikan nama direktori unik untuk menghindari konflik
            $tempDir = storage_path('app/temp_pdf_exports_' . uniqid());
            File::makeDirectory($tempDir, 0755, true); // Buat direktori

            // Buat PDF untuk setiap reimbursement
            foreach ($overtimes as $overtime) {
                // Load view PDF dengan data overtime
                // View harus menggunakan $overtime->employee, bukan Auth::user()
                $pdf = Pdf::loadView('admin.overtime.pdf', compact('overtime'));

                // Buat nama file yang unik dan deskriptif
                // Sanitasi nama file untuk menghindari karakter ilegal
                $safeEmployeeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $overtime->employee->name ?? 'Unknown');
                $fileName = "overtime_{$safeEmployeeName}_RY{$overtime->id}.pdf";
                $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

                // Simpan PDF ke direktori sementara
                $pdf->save($filePath);
            }

            // Buat file ZIP
            $zipFileName = 'overtime-requests-all-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $zip = new ZipArchive();

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                // Tambahkan semua file PDF yang telah dibuat ke dalam ZIP
                $files = File::files($tempDir);
                foreach ($files as $file) {
                    // Tambahkan file ke ZIP dengan nama file asli
                    $zip->addFile($file->getPathname(), $file->getFilename());
                }
                $zip->close();
            } else {
                // Jika gagal membuat ZIP, hapus direktori sementara dan lempar exception
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
                throw new \Exception('Could not create ZIP file.');
            }

            // Hapus direktori sementara setelah ZIP berhasil dibuat
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            // Return file ZIP sebagai download
            // deleteFileAfterSend(true) akan menghapus file ZIP setelah dikirim ke browser
            return response()->download($zipFilePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Export PDF All Data error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'filters' => $request->only(['status', 'from_date', 'to_date'])
            ]);

            // Hapus file/direktori sementara jika ada error di tengah jalan
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            if (isset($zipFilePath) && File::exists($zipFilePath)) {
                File::delete($zipFilePath);
            }

            // Return JSON error response untuk AJAX
            return response()->json([
                'error' => 'Export PDF (All) failed: ' . $e->getMessage()
            ], 500);
        }
    }

}

