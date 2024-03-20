<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Widgets;

use App\Models\DateEvent;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonitoredPropertyPricesOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    protected function getData(): array
    {
        $prices = MonitoredData::query()
            ->where('monitored_property_id', $this->record->id)
            ->groupBy('checkin')
            ->whereDate('checkin', '>', now())
            ->get();

        $biggestValue = $prices->max('price');

        $eventDays = $prices->pluck('checkin')
            ->mapWithKeys(
                fn ($date) => [$date->toDateString() => 0]
            );

        DateEvent::query()
            ->whereDate('begin', '>', now())
            ->get()
            ->each(
                function ($event) use (&$eventDays, $biggestValue) {
                    $numberOfDays = $event->begin->diffInDays($event->end);
                    $date = $event->begin->copy();
                    for ($i = 0; $i <= $numberOfDays; $i++) {
                        $dateString = $date->toDateString();
                        data_set($eventDays, $dateString, $biggestValue);
                        $date->addDay();
                    }
                }
            );

        $notificationsDays = $prices->pluck('checkin')
            ->mapWithKeys(
                fn ($date) => [$date->toDateString() => 0]
            );

        PriceNotification::query()
            ->where('monitored_property_id', $this->record->id)
            ->whereDate('checkin', '>', now())
            ->groupBy('checkin')
            ->get()
            ->each(
                fn ($event) => data_set($notificationsDays, $event->checkin->toDateString(), $biggestValue)
            );

        return [
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Notifications',
                    'data' => $notificationsDays->values()->toArray(),
                    'backgroundColor' => '#9B10F5',
                    'borderColor' => '#9B10F5',
                ],
                [
                    'type' => 'line',
                    'label' => 'Prices',
                    'data' => $prices->pluck('price')->toArray(),
                ],
                [
                    'type' => 'bar',
                    'label' => 'Events',
                    'data' => $eventDays->values()->toArray(),
                    'backgroundColor' => '#9BD0F5',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $prices->pluck('checkin')->map(
                fn ($date) => $date->format('D, d M y')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
