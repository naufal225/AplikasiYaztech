<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $search = request('search');
        $customers = Customer::latest()
            ->paginate(10);
        return view('admin.customer.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',

        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',
            'name.unique' => 'The name must be unique.',
        ]);

        Customer::create([
            "name" => $validated["name"],
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Successfully create customer.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customer.update', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,'.$customer->id.',id',

        ], [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than :max characters.',
            'name.unique' => 'The name must be unique.',
        ]);

        $customer->update([
            "name" => $validated["name"],
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Successfully update customer.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Successfully delete customer.');
    }
}
