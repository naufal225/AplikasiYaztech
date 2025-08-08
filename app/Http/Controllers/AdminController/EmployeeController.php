<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class EmployeeController extends Controller
{
    public function index() {
        $search = request('search');
        $employees = User::where('role', Roles::Employee->value)->where('name', 'like', '%' . $search . '%')->latest()->paginate(15);
        return view('admin.employee.index', compact('employees'));
    }

    public function create() {
        return view('admin.employee.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email'
        ]);

        $user = User::create([
            "email" => $validated["email"],
            "name" => $validated["name"],
            "role" => Roles::Employee->value,
            "password" => bcrypt("password")
        ]);

        Password::sendResetLink(["email" => $user->email]);

        return redirect()->route('admin.employee.index')->with('success', 'Successfully create employee.');
    }

    public function edit(User $employee) {

    }
}
