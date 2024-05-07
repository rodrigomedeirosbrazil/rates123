<?php

namespace App\Filament\Shared\Resources\RateGraphResource\Widgets;

use App\Models\Rate;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\On;

class RatesOverview extends ApexChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $contentHeight = 500; //px

    protected static ?string $chartId = 'propertySyncOverview';

    protected static ?string $pollingInterval = null;

    public ?Model $record = null;

    public array $filters = [];

    #[On('property-filter-changed')]
    public function filtersUpdated($filters): void
    {
        $this->filters = $filters;
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        if (! $this->getFilter('is_filtered', false)) {
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
                        'name' => 'Rates',
                        'data' => [],
                    ],
                ],
                'stroke' => [
                    'curve' => 'smooth',
                ],
                'yaxis' => [
                    [
                        'title' => [
                            'text' => 'Rates',
                        ],
                    ],
                ],
                'legend' => [
                    'labels' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ];
        }

        $rates = Rate::query()
            ->where('property_id', $this->getFilter('property_id'))
            ->whereDate('checkin', '>', today()->startOfMonth()->startOfDay())
            ->whereDate('checkin', '<', today()->endOfMonth()->endOfDay())
            ->where('available', true)
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
            ->sortBy('checkin')
            ->values();


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
                    'name' => 'Rates',
                    'data' => $rates->map(
                        fn ($rate) => [
                            'x' => $rate->checkin->translatedFormat('D, d M y'),
                            'y' => $rate->price,
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
                        'text' => 'Rates',
                    ],
                ],
            ],
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }

    protected function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->filters, $key, $default);
    }
}
