<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:approver'])->prefix('approver')->name('approver.')->group(function () {

});
