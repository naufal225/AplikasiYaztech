<?php

namespace App\Http\Controllers;

use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\returnSelf;

class AuthController extends Controller
{
    public function index() {
        if(Auth::check()) {
            $user = Auth::user();
            
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
        }

        // If not authenticated, show the login page
        return view('Auth.index');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            "email" => "required|email:dns",
            "password" => "required|string"
        ]);

        if(!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->with("error", "Email atau password anda tidak valid");
        }

        session()->regenerate();

        $user = Auth::user();

        dd($user);

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
    }

    public function logout() {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
