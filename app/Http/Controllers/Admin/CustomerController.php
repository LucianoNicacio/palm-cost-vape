<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->withCount('reservations');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by subscription
        if ($request->has('subscribed')) {
            $query->where('is_subscribed', $request->boolean('subscribed'));
        }

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $query->orderBy('created_at', 'desc')
                ->paginate(25)
                ->withQueryString(),
            'stats' => [
                'total' => Customer::count(),
                'subscribed' => Customer::subscribed()->count(),
                'new_this_month' => Customer::newThisMonth()->count(),
            ],
            'filters' => $request->only(['search', 'subscribed']),
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'reservations' => fn($q) => $q->with('items')->latest()->take(20)
        ]);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'is_subscribed' => 'boolean',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer updated.');
    }

    public function export(Request $request)
    {
        $customers = $request->boolean('subscribed_only')
            ? Customer::subscribed()->get()
            : Customer::all();

        // Build CSV
        $csv = "Name,Email,Phone,Subscribed,Total Orders,Total Spent,Created\n";

        foreach ($customers as $c) {
            $csv .= sprintf(
                '"%s","%s","%s",%s,%d,%.2f,"%s"' . "\n",
                $c->name,
                $c->email,
                $c->phone ?? '',
                $c->is_subscribed ? 'Yes' : 'No',
                $c->total_reservations,
                $c->total_spent,
                $c->created_at->format('Y-m-d')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="customers-' . date('Y-m-d') . '.csv"');
    }
}