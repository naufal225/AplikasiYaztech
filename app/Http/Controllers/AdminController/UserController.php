<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Division;
use App\Models\Leave;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index()
    {
        $search = request('search');
        $users = User::where('role', '!=', Roles::Admin->value)
            ->where('name', 'like', '%' . $search . '%')
            ->orderByRaw("
                CASE role
                    WHEN ? THEN 1
                    WHEN ? THEN 2
                    WHEN ? THEN 3
                END
            ", [
                Roles::Manager->value,
                Roles::Approver->value,
                Roles::Employee->value
            ])
            ->latest()
            ->paginate(10);
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        $divisions = Division::latest()->get();
        $roles = collect(Roles::cases())
            ->reject(fn($role) => $role == Roles::Admin)
            ->values();
        return view('admin.user.create', compact('divisions', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email',
            'role' => 'required|string|in:employee,approver,manager',
            'division_id' => 'required|exists:divisions,id'

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
            "role" => $validated['role'],
            'division_id' => $validated['division_id'],
            "password" => bcrypt("password")
        ]);

        $token = Password::createToken($user);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email, // dibaca di query oleh showResetForm
        ]);

        Mail::to($user->email)->send(new ResetPasswordMail($user->name, $resetUrl));

        return redirect()->route('admin.users.index')->with('success', 'Successfully create user.');
    }

    public function edit(User $user)
    {
        $divisions = Division::latest()->get();
        $roles = collect(Roles::cases())
            ->reject(fn($role) => $role == Roles::Admin)
            ->values();

        return view('admin.user.update', compact('user', 'divisions', 'roles'));
    }

    public function update(User $user, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,' . $user->id . ',id',
            'role' => 'required|string|in:employee,approver,manager',
            'division_id' => 'required|exists:divisions,id'
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',

            'role.required' => 'The role field is required.',
            'role.string' => 'The role must be a valid string.',
            'role.in' => 'The role must be employee, approver or hr.',

            'division_id.required' => 'The division field is required.',
            'division_id.exists' => 'The division field must be exists.'
        ]);

        if (($user->division_id != $validated['division_id'] && $user->division->leader_id == $user->id)) {
            $user->division->leader_id = null;
        }

        $user->update([
            "email" => $validated["email"],
            "name" => $validated["name"],
            'division_id' => $validated['division_id'],
            "role" => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Successfully update user.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Successfully delete user.');
    }

    public function approve(Leave $leave)
    {
        $leave->update([
            'status' => 'approved'
        ]);

        return redirect()->route('approver.leaves.index');
    }

    public function reject(Leave $leave)
    {
        $leave->update([
            'status' => 'rejected'
        ]);

        return redirect()->route('approver.leaves.index');
    }
}
