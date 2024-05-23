<x-filament-panels::page>
    @foreach (auth()->user()->userProperties as $userProperty)
        @livewire('price-notifications-component', ['propertyId' => $userProperty->property->id, 'propertyName' => $userProperty->property->name])
    @endforeach
</x-filament-panels::page>
