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

        return view('manager.reimbursement.index', compact('reimbursements', 'totalRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'manager'));
    }
}
