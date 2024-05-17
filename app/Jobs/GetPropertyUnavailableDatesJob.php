<?php

namespace App\Jobs;

use App\Managers\CheckPriceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetPropertyUnavailableDatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $propertyId,
        public string $propertyName,
    ) {
    }

    public function handle(): void
    {
        (new CheckPriceManager())->getGroupUnavailableConsecutiveDates($this->propertyId)
            ->each(function ($ratesGroup) {
                if (
                    $ratesGroup->isEmpty()
                    || $ratesGroup->first()->checkin->diffInDays($ratesGroup->last()->checkin) < 1
                    || $ratesGroup->first()->checkin->diffInDays($ratesGroup->last()->checkin) > 7
                ) {
                    return;
                }

                GetPropertyUnavailableDatePriceJob::dispatch(
                    propertyId: $ratesGroup->first()->property_id,
                    propertyName: $ratesGroup->first()->property->name,
                    checkin: $ratesGroup->first()->checkin,
                    checkout: $ratesGroup->last()->checkin
                );
            });
    }

    public function tags(): array
    {
        return [
            'property: ' . $this->propertyName,
            'propertyId: ' . $this->propertyId,
        ];
    }
}
