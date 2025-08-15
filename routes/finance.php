<?php

use App\Http\Controllers\FinanceController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('finance.dashboard');
    });
});
