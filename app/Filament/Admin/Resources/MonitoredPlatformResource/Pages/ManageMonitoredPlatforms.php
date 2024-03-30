<?php

namespace App\Filament\Admin\Resources\MonitoredPlatformResource\Pages;

use App\Filament\Admin\Resources\MonitoredPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMonitoredPlatforms extends ManageRecords
{
    protected static string $resource = MonitoredPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
