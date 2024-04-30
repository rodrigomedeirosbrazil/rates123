<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Collection;

class OccupancyNotificationsMail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(
        public User $user,
        public Collection $occupancyNotifications
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('contato@medeirostec.com.br', 'Rates123'),
            subject: __('Rates123 Occupancy Notifications'),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.price-notifications-text',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
