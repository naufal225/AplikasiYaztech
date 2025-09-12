<?php

use App\Http\Controllers\ManagerController\LeaveController;
use App\Http\Controllers\ManagerController\OfficialTravelController;
use App\Http\Controllers\ManagerController\OvertimeController;
use App\Http\Controllers\ManagerController\ReimbursementController;
use App\Http\Controllers\ManagerController\DashboardController;
use App\Http\Controllers\ManagerController\ProfileController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('manager.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::put('/leaves/{leave}/update-self', [LeaveController::class, 'updateSelf'])->name('leaves.updateSelf');
    Route::get('/leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');
    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::get('/reimbursements/export', [ReimbursementController::class, 'export'])
        ->name('reimbursements.export');
    Route::put('/reimbursements/{reimbursement}/update-self', [ReimbursementController::class, 'updateSelf'])->name('reimbursements.updateSelf');
    Route::resource('reimbursements', ReimbursementController::class)
    ->parameters([
        "reimbursements" => "reimbursement"
    ]);

    Route::get('/overtimes/export', [OvertimeController::class, 'export'])
    ->name('overtimes.export');
    Route::put('/overtimes/{overtime}/update-self', [OvertimeController::class, 'updateSelf'])->name('overtimes.updateSelf');
    Route::resource('overtimes', OvertimeController::class);

    Route::get('/official-travels/export', [OfficialTravelController::class, 'export'])
    ->name('official-travels.export');
    Route::put('/official-travels/{officialTravel}/update-self', [OfficialTravelController::class, 'updateSelf'])->name('official-travels.updateSelf');
    Route::resource('official-travels', OfficialTravelController::class);

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);
});
