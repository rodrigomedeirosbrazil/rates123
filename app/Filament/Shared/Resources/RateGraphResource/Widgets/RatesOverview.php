<?php

namespace App\Filament\Shared\Resources\RateGraphResource\Widgets;

use App\Models\Property;
use App\Models\Rate;
use Carbon\Carbon;
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

    #[On('filters-changed')]
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
            $points = $this->getRatesPointsFromProperty(
                property: $property,
                from: Carbon::parse($this->getFilter('from_date'))->startOfDay(),
                to: Carbon::parse($this->getFilter('to_date'))->endOfDay()
            );

            array_push($series, [
                'name' => $property->name,
                'data' => $points,
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

    public function getRatesPointsFromProperty(Property $property, CarbonInterface $from, CarbonInterface $to)
    {
        $ratesWithHoles = Rate::query()
            ->where('property_id', $property->id)
            ->whereDate('checkin', '>=', $from)
            ->whereDate('checkin', '<=', $to)
            ->where('available', true)
            ->groupBy('checkin')
            ->addMax('updated_at')
            ->orderBy('checkin')
            ->get()
            ->values()
            ->mapWithKeys(
                fn (Rate $rate) => [
                    $rate->checkin->toDateString() => [
                        'x' => $rate->checkin->translatedFormat('D, d M y'),
                        'y' => $rate->price,
                    ],
                ]
            );

        $days = collect([]);
        $date = $from->copy();
        $lastRate = $ratesWithHoles->first();

        while ($date->lte($to)) {
            if ($ratesWithHoles->has($date->toDateString())) {
                $lastRate = $ratesWithHoles->get($date->toDateString());
            }

            $days[$date->toDateString()] = [
                'x' => $date->translatedFormat('D, d M y'),
                'y' => $lastRate['y'] ?? null,
            ];

            $date->addDay();
        }

        $points = $days->values()->toArray();

        return $points;
    }
}
