<?php

use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {

});
