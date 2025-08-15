<?php

use App\Http\Controllers\HrController\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {

});
