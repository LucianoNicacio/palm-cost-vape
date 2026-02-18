<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $r = $this->reservation;

        $message = (new MailMessage)
            ->subject("Reservation Update - {$r->confirmation_number}");

        // Customize message based on status
        match ($r->status) {
            'ready' => $message
                ->greeting("Your order is ready for pickup!")
                ->line("Your reservation **{$r->confirmation_number}** is ready.")
                ->line("**Total Due: \${$r->total_price}**")
                ->line("Pickup: Palm Coast Vape, 29 Old Kings Rd N, Suite 2-A, Palm Coast, FL")
                ->line("Please bring valid ID when picking up your order."),

            'completed' => $message
                ->greeting("Thank you for your purchase!")
                ->line("Your reservation **{$r->confirmation_number}** has been completed.")
                ->line("We appreciate your business and hope to see you again!"),

            'cancelled' => $message
                ->greeting("Reservation Cancelled")
                ->line("Your reservation **{$r->confirmation_number}** has been cancelled.")
                ->line("If you have any questions, please contact us at (386) 597-2838."),

            default => $message
                ->greeting("Reservation Update")
                ->line("Status: {$r->status_label}"),
        };

        return $message->action('View', url("/confirmation/{$r->confirmation_number}"));
    }
}