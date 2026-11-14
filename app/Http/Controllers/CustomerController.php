<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index', ['title' => 'Customer Accounts', 'customers' => Customer::query()->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('customers.form', ['title' => 'Create Customer', 'customer' => new Customer]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($request->safe()->only(['full_name', 'email', 'phone']));

        return to_route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', ['title' => 'Edit Customer', 'customer' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->safe()->only(['full_name', 'email', 'phone']));

        return to_route('customers.index')->with('success', 'Customer updated successfully.');
    }
}
