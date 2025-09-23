<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Division;
use App\Models\Leave;
use App\Models\User;
use App\Enums\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $search = request('search');
        $users = User::where('name', 'like', '%' . $search . '%')
            ->whereNotIn('role', [Roles::SuperAdmin->value])
            ->orderByRaw("
                CASE role
                    WHEN ? THEN 1
                    WHEN ? THEN 2
                    WHEN ? THEN 3
                    WHEN ? THEN 4
                    WHEN ? THEN 5
                END
            ", [
                Roles::Admin->value,
                Roles::Manager->value,
                Roles::Approver->value,
                Roles::Employee->value,
                Roles::Finance->value
            ])
            ->latest()
            ->paginate(10);
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        $divisions = Division::latest()->get();
        $roles = collect(Roles::cases())
            ->values();
        $roleLabels = [
            Roles::Approver->value => 'Approver 1',
            Roles::Employee->value => 'Employee',
            Roles::Manager->value => 'Approver 2',
            Roles::Admin->value => 'Admin',
            Roles::SuperAdmin->value => 'Super Admin',
            Roles::Finance->value => 'Approver 3',
        ];

        return view('admin.user.create', compact('divisions', 'roles', 'roleLabels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email',
            'role' => 'required|string|in:employee,approver,manager,finance,admin',
            'division_id' => 'required|exists:divisions,id'

        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.'
        ]);

        if ($validated['role'] === Roles::Approver->value) {
            // Pastikan division_id ada
            if (empty($validated['division_id'])) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division is required when assigning role approver.'
                ]);
            }

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division not found for approver role.'
                ]);
            }
        }

        $user = User::create([
            "email" => $validated["email"],
            "name" => $validated["name"],
            "role" => $validated['role'],
            'division_id' => $validated['division_id'],
            "password" => bcrypt("password")
        ]);

        if ($validated['role'] === Roles::Approver->value) {
            // Pastikan division_id ada
            if (empty($validated['division_id'])) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division is required when assigning role approver.'
                ]);
            }

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division not found for approver role.'
                ]);
            }

            // Kalau divisi belum ada leader
            if ($division->leader_id === null) {
                $division->update(['leader_id' => $user->id]);
            } else {
                // Kalau sudah ada leader → overwrite
                $oldLeader = $division->leader;
                if ($oldLeader) {
                    $oldLeader->update(['role' => Roles::Employee->value]);
                }
                $division->update(['leader_id' => $user->id]);
            }
        }


        $token = Password::createToken($user);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email, // dibaca di query oleh showResetForm
        ]);

        Mail::to($user->email)->queue(new ResetPasswordMail($user->name, $resetUrl));

        return redirect()->route('admin.users.index')->with('success', 'Successfully create user.');
    }

    public function edit(User $user)
    {
        $divisions = Division::latest()->get();
        $roles = collect(Roles::cases())
            ->values();

        $roleLabels = [
            Roles::Approver->value => 'Approver 1',
            Roles::Employee->value => 'Employee',
            Roles::Manager->value => 'Approver 2',
            Roles::Admin->value => 'Admin',
            Roles::SuperAdmin->value => 'Super Admin',
            Roles::Finance->value => 'Approver 3',
        ];

        return view('admin.user.update', compact('user', 'divisions', 'roles', 'roleLabels'));
    }

    public function update(User $user, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,' . $user->id . ',id',
            'role' => 'required|string|in:employee,approver,manager,finance,admin',
            'division_id' => 'exists:divisions,id'
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

        // jika pindah divisi padahal dia leader, copot dia
        if (($user->division_id != $validated['division_id'] && $user->division != null && $user->division->leader_id !== null)) {
            if ($user->division->leader_id == $user->id) {
                $user->division->update(['leader_id' => null]);
            }
        }

        if ($validated['role'] === Roles::Approver->value) {
            // Pastikan division_id ada
            if (empty($validated['division_id'])) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division is required when assigning role approver.'
                ]);
            }

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division not found for approver role.'
                ]);
            }
        }

        $role = Auth::user()->role;

        $user->update([
            "email" => $validated["email"],
            "name" => $validated["name"],
            'division_id' => $validated['division_id'],
            "role" => $validated['role'],
        ]);

        if ($validated['role'] === Roles::Approver->value) {
            // Pastikan division_id ada
            if (empty($validated['division_id'])) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division is required when assigning role approver.'
                ]);
            }

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw ValidationException::withMessages([
                    'division_id' => 'Division not found for approver role.'
                ]);
            }

            // Kalau divisi belum ada leader
            if ($division->leader_id === null) {
                $division->update(['leader_id' => $user->id]);
            } else {
                // Kalau sudah ada leader → overwrite
                $oldLeader = $division->leader;
                if ($oldLeader) {
                    $oldLeader->update(['role' => Roles::Employee->value]);
                }
                $division->update(['leader_id' => $user->id]);
            }
        }

        if (Auth::id() == $user->id && $role != $user->role) {
            Auth::logout();

            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login');
        }

        return redirect()->route('admin.users.index')->with('success', 'Successfully update user.');
    }

    public function destroy(User $user)
    {
        if ($user->division->leader_id == $user->id) {
            $user->division->update(['leader_id' => null]);
        }

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
