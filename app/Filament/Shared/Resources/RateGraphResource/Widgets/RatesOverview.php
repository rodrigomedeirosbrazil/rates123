<?php

namespace App\Filament\Shared\Resources\RateGraphResource\Widgets;

use App\Models\Property;
use App\Models\Rate;
use Carbon\CarbonInterface;
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
        $properties = Property::query()
            ->select('id', 'name')
            ->whereIn('id', $this->getFilter('property_id'))
            ->get();

        $series = [];

        foreach ($properties as $property) {
            $rates = $this->getRatesFromProperty(
                property: $property,
                from: today()->startOfMonth()->startOfDay(),
                to: today()->endOfMonth()->endOfDay()
            );

            array_push($series, [
                'name' => $property->name,
                'data' => $rates->map(
                    fn ($rate) => [
                        'x' => $rate->checkin->translatedFormat('D, d M y'),
                        'y' => $rate->price,
                    ]
                )->toArray(),
            ]);
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 400,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => $series,
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

    public function getRatesFromProperty(Property $property, CarbonInterface $from, CarbonInterface $to)
    {
        $cacheKey = "{$property->id}_{$from->toDateString()}_{$to->toDateString()}";

        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $rates = Rate::query()
            ->select('id', 'property_id', 'checkin', 'created_at', 'price')
            ->where('property_id', $property->id)
            ->whereDate('checkin', '>', $from)
            ->whereDate('checkin', '<', $to)
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

        cache()->put($cacheKey, $rates, now()->addMinutes(60));

        return $rates;
    }
}
