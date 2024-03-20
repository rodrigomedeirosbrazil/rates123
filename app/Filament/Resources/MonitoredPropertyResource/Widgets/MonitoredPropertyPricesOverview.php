<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Widgets;

use App\Models\DateEvent;
use App\Models\MonitoredData;
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

        $priceDays = $prices->pluck('checkin')
            ->mapWithKeys(
                fn ($date) => [$date->toDateString() => 0]
            );

        $events = DateEvent::query()
            ->whereDate('begin', '>', now())
            ->get();

        $biggestValue = $prices->max('price');

        $events->each(
            function ($event) use (&$priceDays, $biggestValue) {
                $numberOfDays = $event->begin->diffInDays($event->end);
                $date = $event->begin->copy();
                for ($i = 0; $i <= $numberOfDays; $i++) {
                    $dateString = $date->toDateString();
                    data_set($priceDays, $dateString, $biggestValue);
                    $date->addDay();
                }
            }
        );


        return [
            'datasets' => [
                [
                    'type' => 'line',
                    'label' => 'Prices',
                    'data' => $prices->pluck('price')->toArray(),
                ],
                [
                    'type' => 'bar',
                    'label' => 'Events',
                    'data' => $priceDays->values()->toArray(),
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
