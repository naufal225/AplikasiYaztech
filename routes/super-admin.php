<?php


use App\Http\Controllers\SuperAdminController\ApproverController;
use App\Http\Controllers\SuperAdminController\CustomerController;
use App\Http\Controllers\SuperAdminController\DashboardController;
use App\Http\Controllers\SuperAdminController\DivisionController;
use App\Http\Controllers\SuperAdminController\UserController;
use App\Http\Controllers\SuperAdminController\LeaveController;
use App\Http\Controllers\SuperAdminController\OfficialTravelController;
use App\Http\Controllers\SuperAdminController\OvertimeController;
use App\Http\Controllers\SuperAdminController\ProfileController;
use App\Http\Controllers\SuperAdminController\ReimbursementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superAdmin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('super-admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('divisions', DivisionController::class);

    Route::resource('users', UserController::class);

    Route::get('/leaves/export', [LeaveController::class, 'export'])
        ->name('leaves.export');

    Route::get('leaves/{leave}/export-pdf', [LeaveController::class, 'exportPdf'])->name('leaves.exportPdf');

    Route::resource('leaves', LeaveController::class)
        ->parameters([
            "leaves" => "leave"
        ]);

    Route::get('/reimbursements/export', [ReimbursementController::class, 'export'])
        ->name('reimbursements.export');
    Route::get('reimbursements/{reimbursement}/export-pdf', [ReimbursementController::class, 'exportPdf'])->name('reimbursements.exportPdf');
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

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);
});
