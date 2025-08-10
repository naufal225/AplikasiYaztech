<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Roles;
use Illuminate\Support\Facades\Password;

class ApproverController extends Controller
{

    public function index()
    {
        $search = request('search');
        $approvers = User::where('role', Roles::Approver->value)->where('name', 'like', '%' . $search . '%')->latest()->paginate(10);
        return view('admin.approver.index', compact('approvers'));
    }

    public function create()
    {
        return view('admin.approver.create');
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
            "role" => Roles::Approver->value,
            "password" => bcrypt("password")
        ]);

        // Password::sendResetLink(["email" => $user->email]);

        return redirect()->route('admin.approvers.index')->with('success', 'Successfully create approver.');
    }

    public function edit(User $approver)
    {
        return view('admin.approver.update', compact('approver'));
    }

    public function update(User $approver, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:dns|unique:users,email,' . $approver->id . ',id'
        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.'
        ]);

        $approver->update([
            "email" => $validated["email"],
            "name" => $validated["name"],
            "role" => Roles::Approver->value,
        ]);

        return redirect()->route('admin.approvers.index')->with('success', 'Successfully update approver.');
    }

    public function destroy(User $approver)
    {
        $approver->delete();

        return redirect()->route('admin.approvers.index')->with('success', 'Successfully delete approver.');
    }
}
