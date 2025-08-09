<?php

use App\Http\Controllers\AdminController\ApproverController;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee:id}', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee:id}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee:id}', [EmployeeController::class, 'destroy'])->name('delete');
    });

    Route::prefix('approver')->name('approver.')->group(function () {
        Route::get('/', [ApproverController::class, 'index'])->name('index');
        Route::get('/create', [ApproverController::class, 'create'])->name('create');
        Route::post('/', [ApproverController::class, 'store'])->name('store');
        Route::get('/{approver:id}', [ApproverController::class, 'edit'])->name('edit');
        Route::put('/{approver:id}', [ApproverController::class, 'update'])->name('update');
        Route::delete('/{approver:id}', [ApproverController::class, 'destroy'])->name('delete');
    });
});
