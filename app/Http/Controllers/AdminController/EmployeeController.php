<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index() {
        $employees = User::where('role', Roles::Employee->value)->latest()->paginate(15);
        return view('admin.employee.index', compact('employees'));
    }

    public function create() {
        return view('admin.employee.create');
    }

    public function store(Request $request) {

    }

    public function edit(User $employee) {

    }
}
