<?php

namespace App\Mail;

use App\Managers\PriceManager;
use App\Models\PriceNotification;
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
        $this->priceNotificationsTextTable = $this->buildPriceNotificationsTextTable();
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

    public function buildPriceNotificationsTextTable(): ?string
    {
        $priceNotifications = (new PriceManager())->getUserPriceNotifications($this->user, now());

        if ($priceNotifications->isEmpty()) {
            return null;
        }

        return $priceNotifications->map(
            fn (PriceNotification $priceNotification) => [
                __('Checkin') . ': ' . $priceNotification->checkin->translatedFormat('l, d F y') . PHP_EOL,
                __('Property') . ': ' . $priceNotification->monitoredProperty->name . PHP_EOL,
                __('Type') . ': ' . __($priceNotification->type->value) . PHP_EOL,
                __('Before') . ': $' . __($priceNotification->before) . PHP_EOL,
                __('After') . ': $' . __($priceNotification->after) . PHP_EOL,
                __('Change') . ': ' . __($priceNotification->change_percent) . '%' . PHP_EOL,
                PHP_EOL,
            ]
        )->flatten()->implode('');
    }
}
