<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Customer;
use App\Roles;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $reimbursements = Reimbursement::where('employee_id', $user->id)
            ->with(['approver', 'customer']) // Load relationships
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // For statistics
        $totalRequests = $reimbursements->total();
        $pendingRequests = Reimbursement::where('employee_id', $user->id)->where('status', 'pending')->count();
        $approvedRequests = Reimbursement::where('employee_id', $user->id)->where('status', 'approved')->count();
        $rejectedRequests = Reimbursement::where('employee_id', $user->id)->where('status', 'rejected')->count();

        return view('Employee.reimbursements.reimbursement-show', compact('reimbursements', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        $customers = Customer::all(); // Get all customers

        return view('Employee.reimbursements.reimbursement-request', compact('approvers', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $reimbursement = new Reimbursement();
        $reimbursement->employee_id = Auth::id();
        $reimbursement->approver_id = $request->approver_id;
        $reimbursement->customer_id = $request->customer_id;
        $reimbursement->total = $request->total;
        $reimbursement->date = $request->date;
        $reimbursement->status = 'pending';

        if ($request->hasFile('invoice_path')) {
            $path = $request->file('invoice_path')->store('reimbursement_invoices', 'public');
            $reimbursement->invoice_path = $path;
        }

        $reimbursement->save();

        // Send notification email to the approver
        $approver = User::find($request->approver_id);
        if ($approver) {
            $linkTanggapan = route('employee.reimbursements.show', $reimbursement->id);
            $pesan = "Pengajuan reimbursement baru dari $namaPengaju. <br> Total: Rp " . number_format($reimbursement->total) . "<br> Tanggal: {$request->date}";

            Illuminate\Support\Facades\Mail::to($approver->email)->send(new \App\Mail\SendMessage(
                Auth::user()->name,
                $pesan,
                $approver->name,
                $linkTanggapan,
                Auth::user()->email
            ));
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

        return view('Employee.reimbursements.reimbursement-show', compact('reimbursement'));
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
        if ($reimbursement->status !== 'pending') {
            return redirect()->route('employee.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot edit a reimbursement request that has already been processed.');
        }

        $approvers = User::where('role', Roles::Approver->value)
            ->get();
        $customers = Customer::all();

        return view('Employee.reimbursements.reimbursement-edit', compact('reimbursement', 'approvers', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reimbursement $reimbursement)
    {
        // Check if the user has permission to update this reimbursement
        $user = Auth::user();
        if ($user->id !== $reimbursement->employee_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow updating if the reimbursement is still pending
        if ($reimbursement->status !== 'pending') {
            return redirect()->route('employee.reimbursements.show', $reimbursement->id)
                ->with('error', 'You cannot update a reimbursement request that has already been processed.');
        }

        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'total' => 'required|numeric|min:0',
            'date' => 'required|date',
            'invoice_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $reimbursement->approver_id = $request->approver_id;
        $reimbursement->customer_id = $request->customer_id;
        $reimbursement->total = $request->total;
        $reimbursement->date = $request->date;
        $reimbursement->status = 'pending';

        if ($request->hasFile('invoice_path')) {
            // Delete old invoice_path if exists
            if ($reimbursement->invoice_path) {
                Storage::disk('public')->delete($reimbursement->invoice_path);
            }
            $path = $request->file('invoice_path')->store('reimbursement_invoices', 'public');
            $reimbursement->invoice_path = $path;
        } elseif ($request->input('remove_invoice_path')) {
            // Remove invoice_path if checkbox is checked
            if ($reimbursement->invoice_path) {
                Storage::disk('public')->delete($reimbursement->invoice_path);
                $reimbursement->invoice_path = null;
            }
        }

        $reimbursement->save();

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
        if ($reimbursement->status !== 'pending') {
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