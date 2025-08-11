<?php

use App\Http\Controllers\AdminController\ApproverController;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\EmployeeController;
use App\Http\Controllers\AdminController\LeaveController;
use App\Http\Controllers\AdminController\OfficialTravelController;
use App\Http\Controllers\AdminController\OvertimeController;
use App\Http\Controllers\AdminController\ReimbursementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', EmployeeController::class);

    Route::resource('approvers', ApproverController::class);


    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::get('/reimbursements/export', [ReimbursementController::class, 'export'])
        ->name('reimbursements.export');
    Route::resource('reimbursements', ReimbursementController::class)
        ->parameters([
            "reimbursements" => "reimbursement"
        ]);

    Route::get('/overtimes/export', [OvertimeController::class, 'export'])
        ->name('overtimes.export');
    Route::resource('overtimes', OvertimeController::class);

    Route::get('/official-travels/export', [OfficialTravelController::class, 'export'])
        ->name('official-travels.export');
    Route::resource('official-travels', OfficialTravelController::class);
});
