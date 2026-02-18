<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationCompleted;
use App\Mail\ReservationReady;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
        $newStatus = $validated['status'];

        // Don't process if status hasn't changed
        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status unchanged.');
        }

        // Build update data
        $updateData = [
            'status' => $newStatus,
            'notes' => $validated['notes'] ?? $reservation->notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ];

        // Set ready_at timestamp when marking as ready
        if ($newStatus === 'ready') {
            $updateData['ready_at'] = now();
        }

        // Set cancelled_at and reason when cancelling
        if ($newStatus === 'cancelled') {
            $updateData['cancelled_at'] = now();
            $updateData['cancellation_reason'] = 'admin_cancelled';
        }

        // Update reservation
        $reservation->update($updateData);

        // Restore stock if cancelled
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($reservation->items as $item) {
                $item->product?->incrementStock($item->quantity);
            }
        }

        // Update customer stats if completed
        if ($newStatus === 'completed') {
            $reservation->customer?->updateStats();
        }

        // Send email notification based on new status
        $this->sendStatusEmail($reservation, $newStatus);

        return back()->with('success', "Status updated to {$reservation->status_label}");
    }

    /**
     * Send the appropriate email based on the new status.
     */
    private function sendStatusEmail(Reservation $reservation, string $status): void
    {
        $email = $reservation->getNotificationEmail();

        if (!$email) {
            return;
        }

        $reservation->load(['customer', 'items']);

        $mailable = match ($status) {
            'ready' => new ReservationReady($reservation),
            'completed' => new ReservationCompleted($reservation),
            'cancelled' => new ReservationCancelled($reservation),
            default => null,
        };

        if ($mailable) {
            Mail::to($email)->send($mailable);
        }
    }
}
