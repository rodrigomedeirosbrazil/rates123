<?php

namespace App\Livewire;

use App\Managers\CheckPriceManager;
use Livewire\Component;

class PriceNotifications extends Component
{
    public int $propertyId;
    public string $propertyName;

    public function render()
    {
        return view('livewire.price-notifications');
    }

    public function getPriceNotifications()
    {
        $cacheKey = 'price-notifications-' . $this->propertyId;

        if (cache()->has($cacheKey)) {
            return cache($cacheKey);
        }

        $priceNotifications = (new CheckPriceManager())->checkPropertyPrices($this->propertyId);

        cache([$cacheKey => $priceNotifications], now()->addHour());

        return $priceNotifications;
    }
}
