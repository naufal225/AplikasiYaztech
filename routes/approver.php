<?php

use App\Http\Controllers\ApproverController\LeaveController;
use App\Http\Controllers\ApproverController\DashboardController;
use App\Http\Controllers\ApproverController\OfficialTravelController;
use App\Http\Controllers\ApproverController\OvertimeController;
use App\Http\Controllers\ApproverController\ProfileController;
use App\Http\Controllers\ApproverController\ReimbursementController;
use App\Models\OfficialTravel;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('approver.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');
    Route::get('leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');

    Route::put('/leaves/{leave}/update-self', [LeaveController::class, 'updateSelf'])->name('leaves.updateSelf');

    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::get('/official-travels/export', [OfficialTravelController::class, 'export'])
        ->name('official-travels.export');
    Route::resource('official-travels', OfficialTravelController::class);
    Route::put('/official-travels/{officialTravel}/update-self', [OfficialTravelController::class, 'updateSelf'])->name('official-travels.updateSelf');

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

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);

});
