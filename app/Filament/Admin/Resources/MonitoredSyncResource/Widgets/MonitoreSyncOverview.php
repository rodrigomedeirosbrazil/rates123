<?php

namespace App\Filament\Admin\Resources\MonitoredSyncResource\Widgets;

use App\Enums\SyncStatusEnum;
use App\Models\MonitoredProperty;
use App\Models\MonitoredSync;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonitoredSyncOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lastSync = MonitoredSync::orderBy('started_at', 'desc')->first();

        return [
            Stat::make(
                'Last sync',
                $lastSync->started_at->toFormattedDateString() . '(' . $lastSync->started_at->diffForHumans() . ')',
            ),
            Stat::make(
                'Properties last synced',
                MonitoredSync::where('status', SyncStatusEnum::Successful->value)
                    ->whereDate('started_at', $lastSync->started_at)
                    ->count()
                . '/' . MonitoredProperty::count()
            ),
        ];
    }
}
