<?php

use App\Http\Controllers\ApproverController\LeaveController;
use App\Http\Controllers\ApproverController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/', function() {
        redirect()->route('approver.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);
});
