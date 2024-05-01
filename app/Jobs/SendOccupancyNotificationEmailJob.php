<?php

namespace App\Jobs;

use App\Mail\OccupancyNotificationsMail;
use App\Managers\OccupancyManager;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOccupancyNotificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(public int $userId)
    {
    }

    public function handle(OccupancyManager $occupancyManager): void
    {
        $user = User::find($this->userId);

        $occupancyNotifications = $occupancyManager->buildOccupancyNotifications($user);

        if (! $occupancyNotifications) {
            return;
        }

        Mail::to($user)->send(new OccupancyNotificationsMail(
            $user,
            $occupancyNotifications->implode('')
        ));
    }
}
