<?php

namespace App\Jobs;

use App\Managers\CheckPriceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPropertyPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(
        public int $propertyId,
        public string $propertyName,
        public string $platformSlug,
    ) {
    }

    public function handle(CheckPriceManager $checkPriceManager): void
    {
        $checkPriceManager->checkPropertyPrices($this->propertyId);
    }

    public function tags(): array
    {
        return [
            'platform: ' . $this->platformSlug,
            'property: ' . $this->propertyName,
            'propertyId: ' . $this->propertyId,
        ];
    }
}
