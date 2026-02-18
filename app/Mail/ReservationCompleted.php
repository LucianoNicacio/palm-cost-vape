<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Thank You for Your Purchase - {$this->reservation->confirmation_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.completed',
            with: [
                'reservation' => $this->reservation,
                'items' => $this->reservation->items,
                'customer' => $this->reservation->customer,
                'storeName' => config('store.name'),
                'storeAddress' => config('store.address'),
                'storePhone' => config('store.phone'),
                'viewUrl' => url("/confirmation/{$this->reservation->confirmation_number}"),
            ],
        );
    }
}
