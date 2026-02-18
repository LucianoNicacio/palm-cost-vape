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
            ->markdown('emails.reservations.confirmation', [
                'reservation' => $r,
                'items' => $r->items,
                'customer' => $r->customer,
                'storeName' => config('store.name'),
                'storeAddress' => config('store.address'),
                'storeCity' => config('store.city'),
                'storePhone' => config('store.phone'),
                'storeHours' => config('store.hours'),
                'viewUrl' => url("/confirmation/{$r->confirmation_number}"),
            ]);
    }
}
