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
            ->subject("Reservation Received - {$r->confirmation_number}")
            ->greeting("Hi {$r->customer->name}!")
            ->line("We've received your reservation and it's being reviewed.")
            ->line("**Confirmation: {$r->confirmation_number}**")
            ->line("**Total Due: \${$r->total_price}**")
            ->line("We'll notify you when your order is ready for pickup.")
            ->line("Pickup: Palm Coast Vape, 29 Old Kings Rd N, Suite 2-A, Palm Coast, FL")
            ->line("Please bring valid ID when picking up your order.")
            ->action('View Reservation', url("/confirmation/{$r->confirmation_number}"));
    }
}