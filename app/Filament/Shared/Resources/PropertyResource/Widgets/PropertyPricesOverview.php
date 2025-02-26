<?php

namespace App\Filament\Shared\Resources\PropertyResource\Widgets;

use App\Models\ScheduleEvent;
use App\Models\Rate;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PropertyPricesOverview extends ApexChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $contentHeight = 500; //px

    protected static ?string $chartId = 'propertySyncOverview';

    protected static ?string $pollingInterval = null;

    public ?Model $record = null;

    protected function getOptions(): array
    {
        $prices = Rate::query()
            ->where('property_id', $this->record->id)
            ->groupBy('checkin')
            ->whereDate('checkin', '>', now())
            ->get();


        $events = ScheduleEvent::query()
            ->whereDate('begin', '>', now())
            ->get();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 400,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Prices',
                    'data' => $prices->map(
                        fn ($price) => [
                            'x' => $price->checkin->format('D, d M y'),
                            'y' => $price->price,
                        ]
                    )->toArray(),
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'yaxis' => [
                [
                    'title' => [
                        'text' => 'Prices',
                    ],
                ],
            ],
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],

            'annotations' => [
                'xaxis' => $events->map(
                    fn ($event) => [
                        'x' => $event->begin->format('D, d M y'),
                        'x2' => $event->begin->eq($event->end) ? null : $event->end->format('D, d M y'),
                        'borderColor' => '#775DD0',
                        'fillColor' => '#775DD0',
                        'strokeDashArray' => 0,
                        'label' => [
                            'offsetY' => -8,
                            'style' => [
                                'color' => '#fff',
                                'background' => '#775DD0',
                            ],
                            'text' => $event->name,
                        ],
                    ],
                )->toArray(),
            ],
        ];
    }
}
