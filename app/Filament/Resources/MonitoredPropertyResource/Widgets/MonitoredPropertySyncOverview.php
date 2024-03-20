<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Widgets;

use App\Models\MonitoredSync;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class MonitoredPropertySyncOverview extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $lastSync = MonitoredSync::query()
            ->where('monitored_property_id', $this->record->id)
            ->orderBy('started_at', 'desc')
            ->first();

        return [
            Stat::make(
                'Last sync',
                $lastSync->started_at->toDateTimeString()
                . '(' . $lastSync->started_at->diffForHumans() . ')',
            )
                ->color($lastSync->successful ? 'success' : 'danger')
                ->description($lastSync->successful ? 'Successful' : 'Failed'),

        ];
    }
}
