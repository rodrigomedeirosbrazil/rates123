<?php

namespace App\Console\Commands;

use App\Jobs\GetPropertyUnavailableDatesJob;
use App\Managers\CheckPriceManager;
use App\Models\Property;
use Illuminate\Console\Command;

class GetUnavailableDatesCommand extends Command
{
    protected $signature = 'property:get-unavailable-dates
                            {propertyId : The property ID}';

    protected $description = 'Try to scrap unavailable dates for a property.';

    public function handle()
    {
        $propertyId = $this->argument('propertyId');
        $property = Property::find($propertyId);

        if (! $property) {
            $this->error("Couldn't find a property with ID {$propertyId}");

            return 1;
        }

        $this->info("Search for unavailable dates for `{$property->name}`");


        if ($property->platform->slug !== 'booking') {
            $this->error('This command is only available for properties from Booking.com');

            return 1;
        }

        (new CheckPriceManager())->getGroupUnavailableConsecutiveDates($propertyId)
            ->each(function ($ratesGroup) {
                if (
                    $ratesGroup->isEmpty()
                    || $ratesGroup->first()->checkin->diffInDays($ratesGroup->last()->checkin) < 1
                    || $ratesGroup->first()->checkin->diffInDays($ratesGroup->last()->checkin) > 7
                ) {
                    return;
                }

                $this->info("Dispatching job to get dates: {$ratesGroup->first()->checkin->format('Y-m-d')} - {$ratesGroup->last()->checkin->format('Y-m-d')}");

                GetPropertyUnavailableDatesJob::dispatch(
                    propertyId: $ratesGroup->first()->property_id,
                    propertyName: $ratesGroup->first()->property->name,
                );
            });
    }
}
