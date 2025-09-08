<?php

namespace App\Http\Controllers;

use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\returnSelf;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            switch ($user->role) {
                case (Roles::SuperAdmin->value):
                    return redirect()->route('super-admin.dashboard');
                case (Roles::Admin->value):
                    return redirect()->route('admin.dashboard');
                case (Roles::Approver->value):
                    return redirect()->route('approver.dashboard');
                case (Roles::Employee->value):
                    return redirect()->route('employee.dashboard');
                case (Roles::Manager->value):
                    return redirect()->route('manager.dashboard');
                case (Roles::Finance->value):
                    return redirect()->route('finance.dashboard');
                default:
                    return abort(403);

            }
        }

        // If not authenticated, show the login page
        return view('auth.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email" => "required|email:dns",
            "password" => "required|string"
        ], [
            "email.required" => "Email tidak boleh kosong",
            "email.email" => "Format email tidak valid",
            "password.required" => "Password tidak boleh kosong"
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password anda tidak valid']);
        }

        session()->regenerate();

        $user = Auth::user();

        switch ($user->role) {
            case (Roles::SuperAdmin->value):
                return redirect()->route('super-admin.dashboard');
            case (Roles::Admin->value):
                return redirect()->route('admin.dashboard');
            case (Roles::Approver->value):
                return redirect()->route('approver.dashboard');
            case (Roles::Employee->value):
                return redirect()->route('employee.dashboard');
            case (Roles::Manager->value):
                return redirect()->route('manager.dashboard');
            case (Roles::Finance->value):
                return redirect()->route('finance.dashboard');
            default:
                return abort(403);

        }
    }

    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
