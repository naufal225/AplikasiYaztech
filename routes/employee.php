<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController\DashboardController;
use App\Http\Controllers\EmployeeController\LeaveController;
use App\Http\Controllers\EmployeeController\ReimbursementController;
use App\Http\Controllers\EmployeeController\OvertimeController;
use App\Http\Controllers\EmployeeController\OfficialTravelController;
use App\Http\Controllers\EmployeeController\ProfileController;

Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function () {
        return redirect()->route('employee.dashboard');
    });


    // Leave Requests
    Route::get('leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');
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


    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);
});
