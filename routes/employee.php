<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController\DashboardController;
use App\Http\Controllers\EmployeeController\LeaveController;
use App\Http\Controllers\EmployeeController\ReimbursementController;
use App\Http\Controllers\EmployeeController\OvertimeController;
use App\Http\Controllers\EmployeeController\OfficialTravelController;
use App\Http\Controllers\EmployeeController\ProfileController;

Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('employee.dashboard');
    });

    // Dashboard
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


    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);
});
