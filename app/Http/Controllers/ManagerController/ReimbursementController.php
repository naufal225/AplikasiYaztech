<?php

namespace App\Http\Controllers\ManagerController;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\User;
use App\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $query = Reimbursement::with(['approver', 'customer'])
            ->where('status_1', '!=', 'pending')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
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
        }


        if ($request->filled('from_date')) {
            $query->where(
                'date',
                '>=',
                Carbon::parse($request->from_date)->startOfDay()->timezone('Asia/Jakarta')
            );
        }

        if ($request->filled('to_date')) {
            $query->where(
                'date',
                '<=',
                Carbon::parse($request->to_date)->endOfDay()->timezone('Asia/Jakarta')
            );
        }

        $reimbursements = $query->paginate(10);
        $totalRequests = Reimbursement::count();
        $pendingRequests = Reimbursement::where('status_1', 'pending')
            ->orWhere('status_2', 'pending')->count();
        $approvedRequests = Reimbursement::where('status_1', 'approved')
            ->where('status_2', 'approved')->count();
        $rejectedRequests = Reimbursement::where('status_1', 'rejected')
            ->orWhere('status_2', 'rejected')->count();

        $manager = User::where('role', Roles::Manager->value)->first();

        Reimbursement::whereNull('seen_by_manager_at')
            ->update(['seen_by_manager_at' => now()]);

        return view('manager.reimbursement.index', compact('reimbursements', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }

    public function show($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);
        $reimbursement->load(['approver', 'customer']);

        return view('manager.reimbursement.show', compact('reimbursement'));
    }

     public function update(Request $request, Reimbursement $reimbursement)
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

        if($request->has('status_1')) {
            $reimbursement->update([
                'status_1' => $validated['status_1'],
                'note_1' => $validated['note_1'] ?? ""
            ]);
            $status = $validated['status_1'];
        } else if($request->has('status_2')) {
            $reimbursement->update([
                'status_2' => $validated['status_2'],
                'note_2' => $validated['note_2'] ?? ""
            ]);
            $status = $validated['status_2'];
        }

        return redirect()->route('manager.reimbursements.index')->with('success', 'Reimbursement request ' . $status . ' successfully.');
    }
}
