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
            'confirmed' => $message
                ->greeting("Good news!")
                ->line("Your reservation has been confirmed."),

            'ready' => $message
                ->greeting("Your order is ready! 🎉")
                ->line("Total Due: \${$r->total_price}"),

            'completed' => $message
                ->greeting("Thanks!")
                ->line("Your reservation has been completed."),

            'cancelled' => $message
                ->greeting("Cancelled")
                ->line("Your reservation has been cancelled."),

            default => $message
                ->greeting("Update")
                ->line("Status: {$r->status_label}"),
        };

        return $message->action('View', url("/confirmation/{$r->confirmation_number}"));
    }
}