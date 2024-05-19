<?php

namespace App\Filament\Shared\Resources\CalendarResource\Widgets;

use App\Models\Occupancy;
use App\Models\ScheduleEvent;
use App\Models\Rate;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Livewire\Attributes\On;
use Saade\FilamentFullCalendar\Data\EventData;
use Illuminate\Support\Number;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\HtmlString;

class CalendarWidget extends FullCalendarWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?Model $property = null;

    public Model|string|null $model = Rate::class;

    public array $filters = [];

    #[On('property-filter-changed')]
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

        $events = ScheduleEvent::query()
            ->where('begin', '>=', $fetchInfo['start'])
            ->where('end', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (ScheduleEvent $dateEvent) => EventData::make()
                    ->id($dateEvent->id)
                    ->title("* {$dateEvent->name}")
                    ->start($dateEvent->begin)
                    ->end($dateEvent->end)
                    ->backgroundColor('#9065C7')
                    ->borderColor('#9065C7')
                    ->allDay(true)
            )
            ->values();

        $hasPermission = auth()->user()->userProperties->contains('id', $this->getFilter('property_id'));

        $occupancies = $hasPermission
            ? Occupancy::query()
                ->when(
                    $this->getFilter('property_id'),
                    fn ($query) => $query->where('property_id', $this->getFilter('property_id'))
                )
                ->where('checkin', '>=', $fetchInfo['start'])
                ->where('checkin', '<=', $fetchInfo['end'])
                ->get()
                ->groupBy('checkin')
                ->map(
                    fn ($group) => $group
                        ->pipe(
                            fn ($occupancies) => group_by_nearby($occupancies, 'occupancyPercent', 'created_at')
                        )
                        ->first()
                )
                ->flatten(1)
                ->map(
                    fn (Occupancy $occupancy) => EventData::make()
                        ->id($occupancy->id)
                        ->title(number_format($occupancy->occupancyPercent, 0) . '%')
                        ->start($occupancy->checkin)
                        ->end($occupancy->checkin)
                        ->backgroundColor('#F87171')
                        ->borderColor('#F87171')
                        ->allDay(true)
                )
                ->values()
            : [];

        $prices = $this->getEloquentQuery()
            ->when(
                $this->getFilter('property_id'),
                fn ($query) => $query->where('property_id', $this->getFilter('property_id'))
            )
            ->where('checkin', '>=', $fetchInfo['start'])
            ->where('checkin', '<=', $fetchInfo['end'])
            ->get()
            ->groupBy('checkin')
            ->map(
                fn ($group) => $group
                    ->pipe(
                        fn ($rates) => group_by_nearby($rates, 'price', 'created_at')
                    )
                    ->first()
            )
            ->flatten(1)
            ->map(
                fn (Rate $rate) => EventData::make()
                    ->id($rate->id)
                    ->title(Number::currency($rate->price))
                    ->start($rate->checkin)
                    ->end($rate->checkin)
                    ->allDay(true)
            )
            ->values();


        return $events->merge($prices)->merge($occupancies)->toArray();
    }

    protected function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->filters, $key, $default);
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function viewAction(): Action
    {
        return parent::viewAction()
            ->modalHeading(__('Details'))
            ->infolist([
                InfolistGrid::make([])->schema([
                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('id')
                                ->label(__('ID')),

                            TextEntry::make('price')
                                ->label(__('Price'))
                                ->formatStateUsing(fn ($state): string => Number::currency($state)),

                            TextEntry::make('checkin')
                                ->label(__('Checkin'))
                                ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),

                            TextEntry::make('created_at')
                                ->label(__('Created At'))
                                ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),

                            TextEntry::make('available')
                                ->label(__('Available'))
                                ->formatStateUsing(fn (string $state): string => $state ? __('Yes') : __('No')),

                        ])
                            ->columns(5),
                    ])
                        ->collapsible(),

                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('id')
                                ->label('')
                                ->formatStateUsing(
                                    fn ($record): HtmlString => new HtmlString(
                                        Rate::where('checkin', $record->checkin)
                                            ->where('property_id', $record->property_id)
                                            ->get()
                                            ->pipe(
                                                fn ($rates) => group_by_nearby($rates, 'price', 'created_at')
                                            )
                                            ->slice(0, 9)
                                            ->map(
                                                fn ($rate) => format_date_with_weekday($rate->created_at)
                                                . ': '
                                                . Number::currency($rate->price),
                                            )
                                            ->join('<br>')
                                    )
                                ),
                        ])
                            ->columns(1),
                    ])
                        ->description(__('Price History')),
                ]),
            ]);
    }

    protected function modalActions(): array
    {
        return [];
    }
}
