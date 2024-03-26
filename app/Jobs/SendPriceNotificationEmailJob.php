<?php

namespace App\Jobs;

use App\Mail\PriceNotificationsMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPriceNotificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct()
    {
    }

    public function handle(): void
    {
        User::whereNotNull('email')->get()->each(function (User $user) {
            Mail::to($user)->send(new PriceNotificationsMail($user));
        });
    }
}
