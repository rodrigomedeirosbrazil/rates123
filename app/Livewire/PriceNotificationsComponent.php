<?php

namespace App\Livewire;

use App\Models\PriceNotification;
use App\Models\Property;
use Livewire\Component;
use Illuminate\Support\Collection;

class PriceNotificationsComponent extends Component
{
    public int $propertyId;
    public string $propertyName;
    public Collection $priceNotifications;

    public function render()
    {
        return view('livewire.price-notifications-component');
    }

    public function mount()
    {
        $this->priceNotifications = $this->getTodayPriceNotifications();
    }

    public function getTodayPriceNotifications(): Collection
    {
        $property = Property::findOrFail($this->propertyId);

        $followedPropertyIds = $property->followProperties
            ->pluck('followed_property_id');

        return PriceNotification::query()
            ->whereDate('created_at', today())
            ->whereIn('property_id', $followedPropertyIds)
            ->orderBy('checkin', 'asc')
            ->get();
    }
}
