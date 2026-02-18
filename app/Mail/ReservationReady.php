<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationReady extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Order is Ready for Pickup - {$this->reservation->confirmation_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.ready',
            with: [
                'reservation' => $this->reservation,
                'items' => $this->reservation->items,
                'customer' => $this->reservation->customer,
                'pickupDeadline' => $this->reservation->pickup_deadline,
                'storeName' => config('store.name'),
                'storeAddress' => config('store.address'),
                'storeCity' => config('store.city'),
                'storePhone' => config('store.phone'),
                'storeHours' => config('store.hours'),
                'viewUrl' => url("/confirmation/{$this->reservation->confirmation_number}"),
            ],
        );
    }
}
