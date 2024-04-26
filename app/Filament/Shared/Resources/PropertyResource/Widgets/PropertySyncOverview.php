<?php

namespace App\Filament\Shared\Resources\PropertyResource\Widgets;

use App\Enums\SyncStatusEnum;
use App\Models\Sync;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class PropertySyncOverview extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $lastSync = Sync::query()
            ->where('property_id', $this->record->id)
            ->orderBy('started_at', 'desc')
            ->first();

        return [
            Stat::make(
                'Last sync',
                $lastSync->started_at->toDateTimeString()
                . '(' . $lastSync->started_at->diffForHumans() . ')',
            )
                ->color($lastSync->status === SyncStatusEnum::Successful ? 'success' : 'danger')
                ->description($lastSync->status === SyncStatusEnum::Successful ? 'Successful' : 'Failed'),

        ];
    }
}
