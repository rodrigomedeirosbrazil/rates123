<?php

namespace App\Jobs;

use App\Managers\CheckPriceManager;
use App\Models\Property;
use App\Scraper\BookingScraper;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetPropertyUnavailableDatePriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $propertyId,
        public string $propertyName,
        public CarbonInterface $checkin,
        public CarbonInterface $checkout
    ) {
    }

    public function handle(BookingScraper $bookingScraper): void
    {
        $property = Property::with('platform')
            ->findOrFail($this->propertyId);

        $prices = $bookingScraper->getPriceDetail(
            $property->url,
            $this->checkin,
            $this->checkout
        );

        (new CheckPriceManager())->processPrices($this->propertyId, $prices);
    }

    public function tags(): array
    {
        return [
            'property: ' . $this->propertyName,
            'checkin: ' . $this->checkin->format('Y-m-d'),
            'checkout: ' . $this->checkout->format('Y-m-d'),
            'propertyId: ' . $this->propertyId,
        ];
    }
}
