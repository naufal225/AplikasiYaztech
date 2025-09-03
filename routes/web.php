<?php

use App\Events\SendMessage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/_test-bc', function () {
    $leave = \App\Models\Leave::latest()->first() ?? \App\Models\Leave::factory()->create();
    event(new \App\Events\LeaveSubmitted($leave, 1)); // 👈 harus 1
    return 'sent';
})->middleware('auth');

Route::get('/send-message-test', function() {
    event(new SendMessage(['apa' => 1212121]));
});


Route::middleware('throttle:30,1')->group(function () {
    Route::get('/approve/{token}', [\App\Http\Controllers\PublicApprovalController::class, 'show'])
        ->name('public.approval.show'); // view 2 tombol
    Route::post('/approve/{token}', [\App\Http\Controllers\PublicApprovalController::class, 'act'])
        ->name('public.approval.act'); // eksekusi approve/reject
});


Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/password-reset-success', [ResetPasswordController::class, 'success'])
    ->middleware('guest')
    ->name('password.reset.success');

Route::get('/', function () {
    $user = Auth::user();
    if($user) {
        switch($user->role) {
            case (Roles::SuperAdmin->value) :
                return redirect()->route('super-admin.dashboard');
            case (Roles::Admin->value) :
                return redirect()->route('admin.dashboard');
            case (Roles::Approver->value) :
                return redirect()->route('approver.dashboard');
            case (Roles::Employee->value) :
                return redirect()->route('employee.dashboard');
            case (Roles::Manager->value) :
                return redirect()->route('manager.dashboard');
            case (Roles::Finance->value) :
                return redirect()->route('finance.dashboard');
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

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

require __DIR__.'/super-admin.php';
require __DIR__.'/admin.php';
require __DIR__.'/approver.php';
require __DIR__.'/employee.php';
require __DIR__.'/manager.php';
require __DIR__.'/finance.php';
