<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display customer dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $customer = $user->customer;

        // Get recent orders
        $recentOrders = $customer ? $customer->reservations()
            ->with('items.product')
            ->take(5)
            ->get()
            ->map(fn ($reservation) => [
                'id' => $reservation->id,
                'confirmation_number' => $reservation->confirmation_number,
                'status' => $reservation->status,
                'status_label' => $reservation->status_label,
                'total_price' => $reservation->total_price,
                'item_count' => $reservation->items->count(),
                'created_at' => $reservation->created_at->toDateTimeString(),
            ]) : collect();

        // Get stats
        $stats = [
            'total_orders' => $customer?->total_orders ?? 0,
            'total_spent' => $customer?->total_spent ?? 0,
            'pending_orders' => $customer ? $customer->reservations()
                ->whereIn('status', ['pending', 'ready'])
                ->count() : 0,
        ];

        return Inertia::render('Customer/Dashboard', [
            'recentOrders' => $recentOrders,
            'stats' => $stats,
            'customer' => $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'is_subscribed' => $customer->is_subscribed,
            ] : null,
        ]);
    }
}
