<?php

use App\Enums\Roles;
use App\Http\Controllers\AdminController\ApproverController;
use App\Http\Controllers\AdminController\CostSettingController;
use App\Http\Controllers\AdminController\CustomerController;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\DivisionController;
use App\Http\Controllers\AdminController\HolidayController;
use App\Http\Controllers\AdminController\LeaveBalancesController;
use App\Http\Controllers\AdminController\UserController;
use App\Http\Controllers\AdminController\LeaveController;
use App\Http\Controllers\AdminController\OfficialTravelController;
use App\Http\Controllers\AdminController\OvertimeController;
use App\Http\Controllers\AdminController\ProfileController;
use App\Http\Controllers\AdminController\ReimbursementController;
use App\Http\Controllers\AdminController\ReimbursementTypeController;
use App\Models\OfficialTravel;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
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

    Route::get('/leave-balances/export', [LeaveBalancesController::class, 'exportLeaveBalances'])->name('leave-balances.export');
    Route::resource('leave-balances', LeaveBalancesController::class);

    Route::get('/reimbursements/export', [ReimbursementController::class, 'export'])
        ->name('reimbursements.export');
    Route::get('reimbursements/{reimbursement}/export-pdf', [ReimbursementController::class, 'exportPdf'])->name('reimbursements.exportPdf');
    Route::get('/reimbursements/export/pdf/all', [ReimbursementController::class, 'exportPdfAllData'])
        ->name('reimbursements.export.pdf.all');
    Route::resource('reimbursements', ReimbursementController::class)
        ->parameters([
            "reimbursements" => "reimbursement"
        ]);

    Route::resource('reimbursement-types', ReimbursementTypeController::class)
        ->parameters([
            "reimbursementType" => "reimbursementType"
        ]);

    Route::get('/overtimes/export', [OvertimeController::class, 'export'])
        ->name('overtimes.export');
    Route::get('overtimes/{overtime}/export-pdf', [OvertimeController::class, 'exportPdf'])->name('overtimes.exportPdf');
    Route::get('/overtimes/export/pdf/all', [OvertimeController::class, 'exportPdfAllData'])
        ->name('overtimes.export.pdf.all');
    Route::resource('overtimes', OvertimeController::class);

    Route::get('/official-travels/export', [OfficialTravelController::class, 'export'])
        ->name('official-travels.export');
    Route::get('official-travels/{officialTravel}/export-pdf', [OfficialTravelController::class, 'exportPdf'])->name('official-travels.exportPdf');
    Route::get('/official-travels/export/pdf/all', [OfficialTravelController::class, 'exportPdfAllData'])
        ->name('official-travels.export.pdf.all');
    Route::resource('official-travels', OfficialTravelController::class);

    Route::resource('holidays', HolidayController::class);

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('profile', ProfileController::class);

    // Cost Settings Routes
    Route::get('/cost-settings', [CostSettingController::class, 'index'])->name('cost-settings.index');
    Route::get('/cost-settings/{costSetting}/edit', [CostSettingController::class, 'edit'])->name('cost-settings.edit');
    Route::put('/cost-settings/{costSetting}', [CostSettingController::class, 'update'])->name('cost-settings.update');
    Route::post('/cost-settings/update-multiple', [CostSettingController::class, 'updateMultiple'])->name('cost-settings.update-multiple');

    Route::get('/test', [ReimbursementController::class, 'exportPdfAllData']);
});
