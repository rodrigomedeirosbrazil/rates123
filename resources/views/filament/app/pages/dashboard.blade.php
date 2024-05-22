<x-filament-panels::page>
    @foreach ($this->getPriceNotifications() as $priceNotification)
        @livewire('price-notification', ['priceNotification' => $priceNotification])
    @endforeach
</x-filament-panels::page>
