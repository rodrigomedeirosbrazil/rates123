<x-filament-panels::page>

    <form wire:submit="refreshRecords">
        {{ $this->form }}
    </form>

    @if(! $this->isFiltered())
        <div class="p-4 " role="alert">
            <p class="font-bold">You need to identify the integration.</p>
            <p>First inform an integration ID or select a platform and inform an account ID.</p>
        </div>
    @elseif($totalBookings === 0)
        <div class="p-4" role="alert">
            <p class="font-bold">No bookings found.</p>
            <p>Try to change the filters.</p>
        </div>
    @elseif($totalBookings > 0)
        <div class="p-4" role="alert">
            <p class="font-bold">{{$totalBookings}} booking(s) found.</p>
        </div>
    @endif

    @livewire(\App\Units\Filament\Resources\BookingResource\Widgets\BookingsCalendarWidget::class, ['filters' => $this->filters])

</x-filament-panels::page>
