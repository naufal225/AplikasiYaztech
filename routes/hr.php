<?php

use App\Http\Controllers\ManagerController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});
