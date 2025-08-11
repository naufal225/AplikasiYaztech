<?php

use App\Http\Controllers\ApproverController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
