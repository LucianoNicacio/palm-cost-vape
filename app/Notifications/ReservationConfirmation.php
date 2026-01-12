<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmation extends Notification implements ShouldQueue
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
        $r = $this->reservation->load('items');

        return (new MailMessage)
            ->subject("Reservation Confirmed - {$r->confirmation_number}")
            ->greeting("Hi {$r->customer->name}!")
            ->line("Your reservation has been confirmed.")
            ->line("**Confirmation: {$r->confirmation_number}**")
            ->line("**Total Due: \${$r->total_price}**")
            ->line("Pickup: Palm Coast Vape, 29 Old Kings Rd N, Suite 2-A, Palm Coast, FL")
            ->line("⚠️ Please bring valid ID.")
            ->action('View Reservation', url("/confirmation/{$r->confirmation_number}"));
    }
}