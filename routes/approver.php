<?php

use App\Http\Controllers\ApproverController\LeaveController;
use App\Http\Controllers\ApproverController\DashboardController;
use App\Http\Controllers\ApproverController\OfficialTravelController;
use App\Http\Controllers\ApproverController\OvertimeController;
use App\Http\Controllers\ApproverController\ReimbursementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('approver.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::get('/official-travels/export', [OfficialTravelController::class, 'export'])
        ->name('official-travels.export');
    Route::resource('official-travels', OfficialTravelController::class);

    Route::get('/reimbursements/export', [ReimbursementController::class, 'export'])
        ->name('reimbursements.export');
    Route::resource('reimbursements', ReimbursementController::class)
        ->parameters([
            "reimbursements" => "reimbursement"
        ]);

    Route::get('/overtimes/export', [OvertimeController::class, 'export'])
        ->name('overtimes.export');
    Route::resource('overtimes', OvertimeController::class);

});
