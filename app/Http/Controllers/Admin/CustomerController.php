<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reward;
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
            'reservations' => fn($q) => $q->with('items')->latest()->take(20),
        ]);

        $rewards = $customer->rewards()
            ->with('reservation:id,confirmation_number')
            ->orderByDesc('created_at')
            ->get();

        // Calculate spending progress toward next reward
        $totalSpent = (float) $customer->reservations()
            ->where('status', 'completed')
            ->sum('subtotal');
        $totalEarned = (float) $customer->rewards()->earned()->sum('amount');
        $creditedSpending = ($totalEarned / 10) * 100;
        $progressSpent = fmod($totalSpent - $creditedSpending, 100);
        if ($progressSpent < 0) {
            $progressSpent = 0;
        }

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
            'rewardsBalance' => $customer->rewards_balance,
            'rewards' => $rewards,
            'rewardsProgress' => [
                'toward_next' => round($progressSpent, 2),
                'remaining' => round(100 - $progressSpent, 2),
            ],
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

    public function addReward(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999.99',
            'description' => 'required|string|max:255',
        ]);

        $customer->rewards()->create([
            'amount' => $validated['amount'],
            'type' => 'earned',
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Reward of $' . number_format($validated['amount'], 2) . ' added.');
    }

    public function removeReward(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999.99',
            'description' => 'required|string|max:255',
        ]);

        if ($customer->rewards_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Amount exceeds the customer\'s current balance of $' . number_format($customer->rewards_balance, 2) . '.']);
        }

        $customer->rewards()->create([
            'amount' => $validated['amount'],
            'type' => 'redeemed',
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Reward of $' . number_format($validated['amount'], 2) . ' removed.');
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