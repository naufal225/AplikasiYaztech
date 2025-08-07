<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController\DashboardController;


Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});