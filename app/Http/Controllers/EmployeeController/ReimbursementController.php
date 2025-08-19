<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Customer;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Reimbursement::where('employee_id', $user->id)
            ->with(['approver', 'customer'])
            ->orderBy('created_at', 'desc');
        $queryClone = (clone $query);

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
            $query->where('date_start', '>=',
                Carbon::parse($request->from_date)
                    ->startOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where('date_start', '<=',
                Carbon::parse($request->to_date)
                    ->endOfDay()
                    ->timezone('Asia/Jakarta')
            );
        }

        $reimbursements = $query->paginate(10);
        $counts = $queryClone->withFinalStatusCount()->first();

        $totalRequests = (int) $queryClone->count();
        $pendingRequests = (int) $counts->pending;
        $approvedRequests = (int) $counts->approved;
        $rejectedRequests = (int) $counts->rejected;

        $manager = User::where('role', Roles::Manager->value)->first();

        return view('Employee.reimbursements.reimbursement-show', compact('reimbursements', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        $customers = Customer::all();

        return view('Employee.reimbursements.reimbursement-request', compact('approvers', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $reimbursement = new Reimbursement();
        $reimbursement->employee_id = Auth::id();
        $reimbursement->customer_id = $request->customer_id;
        $reimbursement->total = $request->total;
        $reimbursement->date = $request->date;
        $reimbursement->status_1 = 'pending';
        $reimbursement->status_2 = 'pending';

        if ($request->hasFile('invoice_path')) {
            $path = $request->file('invoice_path')->store('reimbursement_invoices', 'public');
            $reimbursement->invoice_path = $path;
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

            $linkTanggapan = route('approver.reimbursements.show', $reimbursement->id);

            $pesan = "Terdapat pengajuan reimbursement baru atas nama " . Auth::user()->name . ".
                <br> Total: Rp " . number_format($reimbursement->total, 0, ',', '.') . "
                <br> Tanggal: {$request->date}";

            Mail::to($reimbursement->approver->email)->queue(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    pesan: $pesan,
                    namaApprover: $reimbursement->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                    attachmentPath: $reimbursement->invoice_path
                )
            );
        }

        return redirect()->route('employee.reimbursements.index')
            ->with('success', 'Reimbursement request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reimbursement $reimbursement)
    {
        // Check if the user has permission to view this reimbursement
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        $reimbursement->load(['approver', 'customer']);

        return view('Employee.reimbursements.reimbursement-detail', compact('reimbursement'));
    }

    /**
     * Export the specified resource as a PDF.
     */
    public function exportPdf(Reimbursement $reimbursement)
    {
        $pdf = Pdf::loadView('Employee.reimbursements.pdf', compact('reimbursement'));
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
            return redirect()->route('employee.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot edit a reimbursement request that has already been processed.');
        }

        $customers = Customer::all();

        return view('Employee.reimbursements.reimbursement-edit', compact('reimbursement', 'customers'));
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
            return redirect()->route('employee.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot update a reimbursement request that has already been processed.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $reimbursement->customer_id = $request->customer_id;
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
            $linkTanggapan = route('approver.reimbursements.show', $reimbursement->id);

            $pesan = "Pengajuan pengajuan reimbursement milik " . Auth::user()->name . " telah dilakukan perubahan data.
                <br> Total: Rp " . number_format($request->total, 0, ',', '.') . "
                <br> Tanggal: {$request->date}";

            Mail::to($reimbursement->approver->email)->send(
                new \App\Mail\SendMessage(
                    namaPengaju: Auth::user()->name,
                    pesan: $pesan,
                    namaApprover: $reimbursement->approver->name,
                    linkTanggapan: $linkTanggapan,
                    emailPengaju: Auth::user()->email,
                    attachmentPath: $reimbursement->invoice_path
                )
            );
        }

        return redirect()->route('employee.reimbursements.show', $reimbursement->id)
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
            return redirect()->route('employee.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot delete a reimbursement request that has already been processed.');
        }

        if ($reimbursement->invoice_path) {
            Storage::disk('public')->delete($reimbursement->invoice_path);
        }
        $reimbursement->delete();

        return redirect()->route('employee.reimbursements.index')
            ->with('success', 'Reimbursement request deleted successfully.');
    }
}
