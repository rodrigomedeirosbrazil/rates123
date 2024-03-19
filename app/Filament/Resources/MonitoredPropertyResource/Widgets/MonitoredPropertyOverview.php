<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Widgets;

use App\Models\MonitoredData;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonitoredPropertyOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public ?Model $record = null;

    protected function getData(): array
    {
        $prices = MonitoredData::query()
            ->where('monitored_property_id', $this->record->id)
            ->groupBy('checkin')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Mean price by dates',
                    'data' => $prices->pluck('price')->toArray(),
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
