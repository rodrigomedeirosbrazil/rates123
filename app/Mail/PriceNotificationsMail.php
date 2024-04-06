<?php

namespace App\Mail;

use App\Managers\PriceManager;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class PriceNotificationsMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $priceNotificationsTextTable;

    public function __construct(
        public User $user
    ) {
        $this->priceNotificationsTextTable = (new PriceManager())->buildPriceNotificationsTextList($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('contato@medeirostec.com.br', 'Rates123'),
            subject: __('Rates123 Price Notifications'),
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
