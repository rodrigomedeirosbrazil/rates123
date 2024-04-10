<?php

namespace App\Filament\Shared\Resources\CalendarResource\Widgets;

use App\Models\MonitoredData;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Livewire\Attributes\On;
use Saade\FilamentFullCalendar\Data\EventData;
use Illuminate\Support\Number;

class CalendarWidget extends FullCalendarWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?Model $property = null;

    public Model|string|null $model = MonitoredData::class;

    public array $filters = [];

    #[On('bookings-filter-changed')]
    public function filtersUpdated($filters): void
    {
        $this->filters = $filters;
        $this->refreshRecords();
    }

    public function fetchEvents(array $fetchInfo): array
    {
        if (! $this->getFilter('is_filtered', false)) {
            return [];
        }

        return $this->getEloquentQuery()
            ->when(
                $this->getFilter('monitored_property_id'),
                fn ($query) => $query->where('monitored_property_id', $this->getFilter('monitored_property_id'))
            )
            ->where('checkin', '>=', $fetchInfo['start'])
            ->where('checkin', '<=', $fetchInfo['end'])
            ->groupBy('checkin')
            ->get()
            ->map(
                fn (MonitoredData $monitoredData) => EventData::make()
                    ->id($monitoredData->id)
                    ->title(Number::currency($monitoredData->price))
                    ->start($monitoredData->checkin)
                    ->end($monitoredData->checkin)
                    ->allDay(true)
            )
            ->toArray();
    }

    protected function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->filters, $key, $default);
    }

    protected function headerActions(): array
    {
        return [];
    }
}
