<?php

use App\Http\Controllers\FinanceController\DashboardController;
use App\Http\Controllers\FinanceController\LeaveController;
use App\Http\Controllers\FinanceController\ReimbursementController;
use App\Http\Controllers\FinanceController\OvertimeController;
use App\Http\Controllers\FinanceController\OfficialTravelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('finance.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leave Requests
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);
    Route::get('leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');

    // Reimbursements
    Route::resource('reimbursements', ReimbursementController::class);
    Route::get('reimbursements/{reimbursement}/export-pdf', [ReimbursementController::class, 'exportPdf'])->name('reimbursements.exportPdf');

    // Overtimes
    Route::resource('overtimes', OvertimeController::class);
    Route::get('overtimes/{overtime}/export-pdf', [OvertimeController::class, 'exportPdf'])->name('overtimes.exportPdf');

    // Official Travels
    Route::resource('official-travels', OfficialTravelController::class);
    Route::get('official-travels/{official_travel}/export-pdf', [OfficialTravelController::class, 'exportPdf'])->name('official-travels.exportPdf');
});
