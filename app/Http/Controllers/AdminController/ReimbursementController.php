<?php

namespace App\Http\Controllers\AdminController;

use App\Exports\ReimbursementsExport;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use App\Models\Reimbursement;
use App\Models\User;
use App\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive; // Tambahkan ini
use Illuminate\Support\Facades\File; // Tambahkan ini untuk manipulasi file sementara

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        // Query for user's own requests (all statuses)
        $ownRequestsQuery = Reimbursement::with(['employee', 'approver'])
            ->where('employee_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Query for all users' requests (excluding own unless approved)
        $allUsersQuery = Reimbursement::with(['employee', 'approver'])
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


        $totalRequests = Reimbursement::count();
        $pendingRequests = Reimbursement::where('status_1', 'pending')
            ->orWhere('status_2', 'pending')->count();
        $approvedRequests = Reimbursement::where('status_1', 'approved')
            ->where('status_2', 'approved')->count();
        $rejectedRequests = Reimbursement::where('status_1', 'rejected')
            ->orWhere('status_2', 'rejected')->count();

        return view('admin.reimbursement.index', compact('allUsersRequests', 'ownRequests', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    public function show($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);
        $reimbursement->load(['approver']);

        return view('admin.reimbursement.show', compact('reimbursement'));
    }

    public function create()
    {
        return view('admin.reimbursement.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer' => 'required',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'customer.required' => 'Customer harus dipilih.',
            'customer.exists' => 'Customer tidak valid.',
            'total.required' => 'Total harus diisi.',
            'total.numeric' => 'Total harus berupa angka.',
            'total.min' => 'Total tidak boleh kurang dari 0.',
            'date.required' => 'Tanggal harus diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'invoice_path.file' => 'File yang diupload tidak valid.',
            'invoice_path.mimes' => 'File harus berupa: jpg, jpeg, png, pdf.',
            'invoice_path.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
        ]);

        DB::transaction(function () use ($request) {
            $reimbursement = new Reimbursement();
            $reimbursement->employee_id = Auth::id();
            $reimbursement->customer = $request->customer;
            $reimbursement->total = $request->total;
            $reimbursement->date = $request->date;
            $reimbursement->status_1 = 'pending';
            $reimbursement->status_2 = 'pending';

            if ($request->hasFile('invoice_path')) {
                $path = $request->file('invoice_path')->store('reimbursement_invoices', 'public');
                $reimbursement->invoice_path = $path;
            }

            $reimbursement->save();

            $token = null;
            // Send notification email to the approver
            if ($reimbursement->approver) {
                $token = \Illuminate\Support\Str::random(48);
                ApprovalLink::create([
                    'model_type' => get_class($reimbursement),   // App\Models\reim$reimbursement
                    'model_id' => $reimbursement->id,
                    'approver_user_id' => $reimbursement->approver->id,
                    'level' => 1, // level 1 berarti arahnya ke team lead
                    'scope' => 'both',             // boleh approve & reject
                    'token' => hash('sha256', $token), // simpan hash, kirim raw
                    'expires_at' => now()->addDays(3),  // masa berlaku
                ]);

            }

            DB::afterCommit(function () use ($reimbursement, $request, $token) {
                $fresh = $reimbursement->fresh(); // ambil ulang (punya created_at dll)
                // dd("jalan");
                event(new \App\Events\ReimbursementSubmitted($fresh, Auth::user()->division_id));

                // Kalau tidak ada approver atau token, jangan kirim email
                if (!$fresh || !$fresh->approver || !$token) {
                    return;
                }

                $linkTanggapan = route('public.approval.show', $token);

                Mail::to($reimbursement->approver->email)->queue(
                    new \App\Mail\SendMessage(
                        namaPengaju: Auth::user()->name,
                        namaApprover: $reimbursement->approver->name,
                        linkTanggapan: $linkTanggapan,
                        emailPengaju: Auth::user()->email,
                        attachmentPath: $reimbursement->invoice_path
                    )
                );
            });

        });

        return redirect()->route('admin.reimbursements.index')
            ->with('success', 'Reimbursement request submitted successfully.');
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

            return Excel::download(new ReimbursementsExport($filters), $filename);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Export error: ' . $e->getMessage());

            // Return JSON error response
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Reimbursement $reimbursement)
    {
        $pdf = Pdf::loadView('admin.reimbursement.pdf', compact('reimbursement'));
        return $pdf->download('reimbursement-details.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reimbursement $reimbursement)
    {
        // Check if the user has permission to edit this reimbursement
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow editing if the reimbursement is still pending
        if ($reimbursement->status_1 !== 'pending' || $reimbursement->status_2 !== 'pending') {
            return redirect()->route('admin.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot edit a reimbursement request that has already been processed.');
        }

        return view('admin.reimbursement.update', compact('reimbursement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($reimbursement->status_1 !== 'pending' || $reimbursement->status_2 !== 'pending') {
            return redirect()->route('admin.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot update a reimbursement request that has already been processed.');
        }

        $request->validate([
            'customer' => 'required',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'customer.required' => 'Customer harus dipilih.',
            'customer.exists' => 'Customer tidak valid.',
            'total.required' => 'Total harus diisi.',
            'total.numeric' => 'Total harus berupa angka.',
            'total.min' => 'Total tidak boleh kurang dari 0.',
            'date.required' => 'Tanggal harus diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'invoice_path.file' => 'File yang diupload tidak valid.',
            'invoice_path.mimes' => 'File harus berupa: jpg, jpeg, png, pdf.',
            'invoice_path.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
        ]);

        $reimbursement->customer = $request->customer;
        $reimbursement->total = $request->total;
        $reimbursement->date = $request->date;
        $reimbursement->status_1 = 'pending';
        $reimbursement->status_2 = 'pending';
        $reimbursement->note_1 = NULL;
        $reimbursement->note_2 = NULL;

        if ($request->hasFile('invoice_path')) {
            if ($reimbursement->invoice_path) {
                Storage::disk('public')->delete($reimbursement->invoice_path);
            }
            $path = $request->file('invoice_path')->store('reimbursement_invoices', 'public');
            $reimbursement->invoice_path = $path;
        } elseif ($request->input('remove_invoice_path')) {
            if ($reimbursement->invoice_path) {
                Storage::disk('public')->delete($reimbursement->invoice_path);
                $reimbursement->invoice_path = null;
            }
        }

        $reimbursement->save();

        // Send notification email to the approver
        if ($reimbursement->approver) {
            $token = \Illuminate\Support\Str::random(48);
            ApprovalLink::create([
                'model_type' => get_class($reimbursement),   // App\Models\reim$reimbursement
                'model_id' => $reimbursement->id,
                'approver_user_id' => $reimbursement->approver->id,
                'level' => 1, // level 1 berarti arahnya ke team lead
                'scope' => 'both',             // boleh approve & reject
                'token' => hash('sha256', $token), // simpan hash, kirim raw
                'expires_at' => now()->addDays(3),  // masa berlaku
            ]);
            $linkTanggapan = route('public.approval.show', $token);

            Mail::to($reimbursement->approver->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    namaApprover: $reimbursement->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                    attachmentPath: $reimbursement->invoice_path
                )
            );
        }

        return redirect()->route('admin.reimbursements.index')
            ->with('success', 'Reimbursement request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reimbursement $reimbursement)
    {
        // Check if the user has permission to delete this reimbursement
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow deleting if the reimbursement is still pending
        if ($reimbursement->status_1 !== 'pending' || $reimbursement->status_2 !== 'pending') {
            return redirect()->route('admin.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot delete a reimbursement request that has already been processed.');
        }

        if ($reimbursement->invoice_path) {
            Storage::disk('public')->delete($reimbursement->invoice_path);
        }
        $reimbursement->delete();

        return redirect()->route('admin.reimbursements.index')
            ->with('success', 'Reimbursement request deleted successfully.');
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

            // Bangun query dasar untuk semua reimbursement
            $query = Reimbursement::with(['employee', 'approver', 'employee.division']);

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
                    // default: tidak filter status
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
            $reimbursements = $query->get();

            if ($reimbursements->isEmpty()) {
                return response()->json(['message' => 'No data found for the selected filters.'], 404);
            }

            // Buat direktori sementara untuk menyimpan PDF
            $tempDir = storage_path('app/temp_pdf_exports_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            // Buat PDF untuk setiap reimbursement
            foreach ($reimbursements as $reimbursement) {

                $pdf = Pdf::loadView('admin.reimbursement.pdf', compact('reimbursement'));

                // Nama file PDF unik
                $fileName = "Reimbursement_{$reimbursement->employee->name}_RY{$reimbursement->id}.pdf";
                $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

                // Simpan PDF ke direktori sementara
                $pdf->save($filePath);
            }

            // Buat file ZIP
            $zipFileName = 'reimbursement-requests-all-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $zip = new ZipArchive();

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                // Tambahkan semua file PDF ke ZIP
                $files = File::files($tempDir);
                foreach ($files as $file) {
                    $zip->addFile($file->getPathname(), $file->getFilename());
                }
                $zip->close();
            } else {
                // Hapus direktori sementara jika ada error
                File::deleteDirectory($tempDir);
                throw new \Exception('Could not create ZIP file.');
            }

            // Hapus direktori sementara setelah ZIP dibuat
            File::deleteDirectory($tempDir);

            // Return ZIP file sebagai download
            return response()->download($zipFilePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Log error
            Log::error('Export PDF All Data error: ' . $e->getMessage(), ['exception' => $e]);

            // Hapus file/direktori sementara jika ada error di tengah jalan
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            if (isset($zipFilePath) && File::exists($zipFilePath)) {
                File::delete($zipFilePath);
            }

            // Return JSON error response
            return response()->json([
                'error' => 'Export PDF (All) failed: ' . $e->getMessage()
            ], 500);
        }

    }
}
