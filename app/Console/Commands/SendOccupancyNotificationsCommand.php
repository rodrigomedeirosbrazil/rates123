<?php

namespace App\Console\Commands;

use App\Jobs\SendOccupancyNotificationEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendOccupancyNotificationsCommand extends Command
{
    protected $signature = 'app:send-occupancies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send occupancy notifications to users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::whereNotNull('email')
            ->with('userProperties')
            ->get()
            ->each(function (User $user) {
                if ($user->userProperties->isEmpty()) {
                    return;
                }

                $this->info("Processing occupancy notification to {$user->name} ({$user->email}).");
                dispatch(new SendOccupancyNotificationEmailJob($user->id));
            });
    }
}
