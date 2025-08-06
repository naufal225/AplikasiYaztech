<?php

use App\Http\Controllers\AuthController;
use App\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();
    if($user) {
        switch($user->role) {
            case (Roles::Admin) :
                return redirect()->route('admin.dashboard');
            case (Roles::Approver) :
                return redirect()->route('approver.dashboard');
            case (Roles::Employee) :
                return redirect()->route('employee.dashboard');
            default:
                return abort(403);

        }
    } else {
        return redirect()->route('login');
    }
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

require __DIR__.'/admin.php';
require __DIR__.'/approver.php';
require __DIR__.'/employee.php';
