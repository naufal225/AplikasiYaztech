<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class EmployeeController extends Controller
{
    public function index()
    {
        $search = request('search');
        $employees = User::where('role', Roles::Employee->value)->where('name', 'like', '%' . $search . '%')->latest()->paginate(10);
        return view('admin.employee.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employee.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email'
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.'
        ]);

        $user = User::create([
            "email" => $validated["email"],
            "name" => $validated["name"],
            "role" => Roles::Employee->value,
            "password" => bcrypt("password")
        ]);

        // Password::sendResetLink(["email" => $user->email]);

        return redirect()->route('admin.employees.index')->with('success', 'Successfully create employee.');
    }

    public function edit(User $employee)
    {
        return view('admin.employee.update', compact('employee'));
    }

    public function update(User $employee, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,' . $employee->id . ',id'
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.'
        ]);

        $employee->update([
            "email" => $validated["email"],
            "name" => $validated["name"],
            "role" => Roles::Employee->value,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Successfully update employee.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Successfully delete employee.');
    }
}
