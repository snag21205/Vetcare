<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search;
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query = $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->withCount('pets')
            ->latest()
            ->paginate(15);

        return Inertia::render('Customer/Index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect()
            ->route('customers.index')
            ->with('success', 'Đã thêm khách hàng thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['pets.appointments' => function ($query) {
            $query->latest()->take(5);
        }]);
        return Inertia::render('Customers/Show', ['customer' => $customer,]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', ['customer' => $customer,]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect()->route('customer.index')
            ->with('success', 'Đã cập nhập thông tin khách hàng!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()
            ->route('customers.index')
            ->with('success', 'Đã xóa khách hàng.');
    }
}
