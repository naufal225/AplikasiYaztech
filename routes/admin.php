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

    Route::resource('leaves', LeaveController::class);

    Route::resource('reimbursements', ReimbursementController::class);

    Route::resource('overtimes', OvertimeController::class);

    Route::resource('official-travels', OfficialTravelController::class);
});
