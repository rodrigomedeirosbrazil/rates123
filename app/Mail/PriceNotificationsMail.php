<?php

namespace App\Mail;

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

    public string $priceNotificationsTextTable;

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

    // Mudar de tabelas e linhas para cards
    public function buildPriceNotificationsTextTable(): string
    {
        $priceNotifications = PriceNotification::query()
            ->whereDate('created_at', now())
            ->get();


        return $priceNotifications->map(
            fn (PriceNotification $priceNotification) => [
                __('Property') . ': ' . $priceNotification->monitoredProperty->name . PHP_EOL,
                __('Type') . ': ' . __($priceNotification->type->value) . PHP_EOL,
                __('Checkin') . ': ' . $priceNotification->checkin->format('Y-m-d') . PHP_EOL,
                __('Link') . ': ' . config('app.url') . "/price-notifications/{$priceNotification->id}" . PHP_EOL,
                PHP_EOL,
            ]
        )->flatten()->implode('');
    }
}
