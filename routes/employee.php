<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController\DashboardController;
use App\Http\Controllers\EmployeeController\LeaveController;
use App\Http\Controllers\EmployeeController\ReimbursementController;
use App\Http\Controllers\EmployeeController\OvertimeController;
use App\Http\Controllers\EmployeeController\OfficialTravelController;


Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leave Requests
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/request', [LeaveController::class, 'create'])->name('create');
        Route::post('/store', [LeaveController::class, 'store'])->name('store');
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
        Route::get('/{leave}/edit', [LeaveController::class, 'edit'])->name('edit');
        Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');
    });

    // Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    
    // Reimbursements
    Route::resource('reimbursements', ReimbursementController::class);
    Route::get('/reimbursements/{reimbursement}/review', [ReimbursementController::class, 'review'])->name('reimbursements.review');
    Route::post('/reimbursements/{reimbursement}/review', [ReimbursementController::class, 'processReview'])->name('reimbursements.process-review');
    
    // Overtimes
    Route::resource('overtimes', OvertimeController::class);
    Route::get('/overtimes/{overtime}/review', [OvertimeController::class, 'review'])->name('overtimes.review');
    Route::post('/overtimes/{overtime}/review', [OvertimeController::class, 'processReview'])->name('overtimes.process-review');
    
    // Official Travels
    Route::resource('official-travels', OfficialTravelController::class);
    Route::get('/official-travels/{officialTravel}/review', [OfficialTravelController::class, 'review'])->name('official-travels.review');
    Route::post('/official-travels/{officialTravel}/review', [OfficialTravelController::class, 'processReview'])->name('official-travels.process-review');
});