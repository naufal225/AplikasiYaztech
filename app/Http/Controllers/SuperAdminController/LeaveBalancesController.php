<?php

namespace App\Http\Controllers\SuperAdminController;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Leave;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveBalancesController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $divisionId = $request->get('division_id');
        $totalCutiTahunan = (int) env('CUTI_TAHUNAN', 20);

        $divisions = Division::orderBy('name')->get();

        $usedByEmployee = Leave::query()
            ->select('employee_id', DB::raw('SUM(DATEDIFF(date_end, date_start) + 1) AS used_days'))
            ->whereYear('date_start', $year)
            ->where('status_1', 'approved')
            ->groupBy('employee_id');

        $employees = User::query()
            ->with('division')
            ->select([
                'users.*',
                DB::raw("$totalCutiTahunan AS total_cuti"),
                DB::raw("COALESCE(used.used_days, 0) AS used_cuti"),
                DB::raw("($totalCutiTahunan - COALESCE(used.used_days, 0)) AS sisa_cuti"),
                DB::raw("CASE WHEN $totalCutiTahunan > 0 THEN (COALESCE(used.used_days, 0) / $totalCutiTahunan) * 100 ELSE 0 END AS percentage")
            ])
            ->joinSub($usedByEmployee, 'used', function ($join) {
                $join->on('users.id', '=', 'used.employee_id');
            })
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->orderBy('name')
            ->get();

        // Hitung data agregat
        $leaveBalances = $employees->map(function ($emp) {
            return [
                'employee' => $emp,
                'total_cuti' => (int) $emp->total_cuti,
                'used_cuti' => (int) $emp->used_cuti,
                'sisa_cuti' => (int) $emp->sisa_cuti,
                'percentage' => round((float) $emp->percentage, 1),
            ];
        });

        $totalEmployees = $leaveBalances->count();
        $avgUsed = $totalEmployees > 0 ? round($leaveBalances->sum('used_cuti') / $totalEmployees, 1) : 0;
        $avgRemain = $totalEmployees > 0 ? round($leaveBalances->sum('sisa_cuti') / $totalEmployees, 1) : 0;

        return view('super-admin.leave-balances.index', compact(
            'leaveBalances',
            'divisions',
            'year',
            'divisionId',
            'totalEmployees',
            'avgUsed',
            'avgRemain',
            'totalCutiTahunan'
        ));
    }


    public function exportLeaveBalances(Request $request)
    {
        $year = $request->get('year', now()->year);
        $divisionId = $request->get('division_id');

        // Get all divisions for filter
        $divisions = Division::all();

        // Get employees with their leave balances
        $employeesQuery = User::with(['division'])
            ->orderBy('name');

        // Apply division filter if selected
        if ($divisionId) {
            $employeesQuery->where('division_id', $divisionId);
        }

        $employees = $employeesQuery->get();

        // Calculate leave balances for each employee
        $leaveBalances = [];
        $totalCutiTahunan = (int) env('CUTI_TAHUNAN', 20);

        foreach ($employees as $employee) {
            // Perbaikan: Gunakan pendekatan manual untuk validasi tanggal
            $usedLeaves = 0;
            $leaveRecords = Leave::where('employee_id', $employee->id)
                ->where('status_1', 'approved')
                ->whereYear('date_start', $year)
                ->get();

            foreach ($leaveRecords as $record) {
                $startDate = \Carbon\Carbon::parse($record->date_start);
                $endDate = \Carbon\Carbon::parse($record->date_end);

                // Validasi: pastikan date_end >= date_start
                if ($endDate->gte($startDate)) {
                    // Gunakan absolute value untuk memastikan hasil positif
                    $days = abs($endDate->diffInDays($startDate)) + 2;
                    $usedLeaves += $days;
                }
            }

            $sisaCuti = max(0, $totalCutiTahunan - $usedLeaves);

            $leaveBalances[] = [
                'employee' => $employee,
                'total_cuti' => $totalCutiTahunan,
                'used_cuti' => $usedLeaves,
                'sisa_cuti' => $sisaCuti,
                'percentage' => $totalCutiTahunan > 0 ? ($usedLeaves / $totalCutiTahunan) * 100 : 0
            ];
        }

        // Get division name for filename
        $divisionName = 'All_Divisions';
        if ($divisionId) {
            $division = Division::find($divisionId);
            $divisionName = $division ? str_replace(' ', '_', $division->name) : 'Unknown_Division';
        }

        // Generate filename
        $filename = "Leave_Balances_{$year}_{$divisionName}.pdf";

        // Generate PDF
        $pdf = Pdf::loadView('super-admin.leave-balances.export', compact('leaveBalances', 'year', 'divisionId', 'divisions'))
            ->setOptions(['isPhpEnabled' => true])
            ->setPaper('A4', 'landscape');

        return $pdf->download($filename);
    }
}
