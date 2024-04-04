<?php

namespace App\Console\Commands;

use App\Jobs\SendPriceNotificationEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendPriceNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send price notifications to users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::whereNotNull('email')
            ->get()
            ->each(function (User $user) {
                $this->info("Sending price notification to {$user->name} ({$user->email}).");
                dispatch(new SendPriceNotificationEmailJob($user->id));
            });
    }
}
