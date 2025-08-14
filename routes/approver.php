<?php

use App\Http\Controllers\ApproverController\LeaveController;
use App\Http\Controllers\ApproverController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/leaves/{leave:id}/approve', [LeaveController::class, 'approve']);
    Route::post('/leaves/{leave:id}/reject', [LeaveController::class, 'reject']);
    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);
});
