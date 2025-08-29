<?php

use App\Http\Controllers\FinanceController\DashboardController;
use App\Http\Controllers\FinanceController\LeaveController;
use App\Http\Controllers\FinanceController\ReimbursementController;
use App\Http\Controllers\FinanceController\OvertimeController;
use App\Http\Controllers\FinanceController\OfficialTravelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function() {
        return redirect()->route('finance.dashboard');
    });


    // Leave Requests
    Route::get('leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');
    Route::get('leaves/bulk-export', [LeaveController::class, 'bulkExport'])->name('leaves.bulkExport');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);


    // Reimbursements
    Route::get('reimbursements/{reimbursement}/export-pdf', [ReimbursementController::class, 'exportPdf'])->name('reimbursements.exportPdf');
    Route::resource('reimbursements', ReimbursementController::class);


    // Overtimes
    Route::get('overtimes/{overtime}/export-pdf', [OvertimeController::class, 'exportPdf'])->name('overtimes.exportPdf');
    Route::resource('overtimes', OvertimeController::class);


    // Official Travels
    Route::get('official-travels/{official_travel}/export-pdf', [OfficialTravelController::class, 'exportPdf'])->name('official-travels.exportPdf');
    Route::resource('official-travels', OfficialTravelController::class);
});
