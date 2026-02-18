<?php

namespace App\Console\Commands;

use App\Mail\ReservationCancelled;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CancelExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reservations:cancel-expired';

    /**
     * The console command description.
     */
    protected $description = 'Cancel reservations that have been ready for pickup for more than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredReservations = Reservation::with(['customer', 'items'])
            ->expiredPickup()
            ->get();

        if ($expiredReservations->isEmpty()) {
            $this->info('No expired reservations found.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($expiredReservations as $reservation) {
            // Update status to cancelled
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'auto_expired',
            ]);

            // Restore stock for each item
            foreach ($reservation->items as $item) {
                $item->product?->incrementStock($item->quantity);
            }

            // Send cancellation email
            $email = $reservation->getNotificationEmail();
            if ($email) {
                Mail::to($email)->send(new ReservationCancelled($reservation));
            }

            Log::info("Auto-cancelled expired reservation: {$reservation->confirmation_number}", [
                'reservation_id' => $reservation->id,
                'ready_at' => $reservation->ready_at,
                'customer_email' => $email,
            ]);

            $count++;
        }

        $this->info("Cancelled {$count} expired reservation(s).");

        return self::SUCCESS;
    }
}
