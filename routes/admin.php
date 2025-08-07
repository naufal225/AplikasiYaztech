<?php

use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/employee', [EmployeeController::class, 'store'])->name('store');
        Route::get('/edit/{employee:id}', [EmployeeController::class, 'edit'])->name('edit');
    });
});
