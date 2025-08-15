<?php

use App\Http\Controllers\ManagerController\LeaveController;
use App\Http\Controllers\ManagerController\OfficialTravelController;
use App\Http\Controllers\ManagerController\OvertimeController;
use App\Http\Controllers\ManagerController\ReimbursementController;
use App\Http\Controllers\ManagerController\DashboardController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('manager.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::resource('official-travels', OfficialTravelController::class);

    Route::resource('reimbursements', ReimbursementController::class)
        ->parameters([
            "reimbursements" => "reimbursement"
        ]);

    Route::resource('overtimes', OvertimeController::class);
});
