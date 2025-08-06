<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/admin.php';
require __DIR__.'/approver.php';
require __DIR__.'/employee.php';
