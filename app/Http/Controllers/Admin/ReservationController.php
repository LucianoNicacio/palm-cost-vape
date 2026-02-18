<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Notifications\ReservationStatusUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['customer', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter by date
        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today' => $query->today(),
                'week' => $query->thisWeek(),
                'month' => $query->thisMonth(),
                'year' => $query->thisYear(),
                default => null,
            };
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('confirmation_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('created_at', 'desc');

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $query->paginate(25)->withQueryString(),
            'statusCounts' => [
                'all' => Reservation::count(),
                'pending' => Reservation::status('pending')->count(),
                'ready' => Reservation::status('ready')->count(),
                'completed' => Reservation::status('completed')->count(),
                'cancelled' => Reservation::status('cancelled')->count(),
            ],
            'filters' => $request->only(['status', 'date_filter', 'search']),
        ]);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'items.product']);

        return Inertia::render('Admin/Reservations/Show', [
            'reservation' => $reservation,
            'customerHistory' => Reservation::where('customer_id', $reservation->customer_id)
                ->where('id', '!=', $reservation->id)
                ->latest()
                ->take(5)
                ->get(),
            'statuses' => [
                'pending' => 'Pending',
                'ready' => 'Ready for Pickup',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
        ]);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $reservation->status;

        // Update reservation
        $reservation->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $reservation->notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Restore stock if cancelled
        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($reservation->items as $item) {
                $item->product?->incrementStock($item->quantity);
            }
        }

        // Update customer stats if completed
        if ($validated['status'] === 'completed') {
            $reservation->customer->updateStats();
        }

        // Notify customer if status changed
        if ($oldStatus !== $validated['status']) {
            $reservation->customer->notify(
                new ReservationStatusUpdated($reservation)
            );
        }

        return back()->with('success', "Status updated to {$reservation->status_label}");
    }
}