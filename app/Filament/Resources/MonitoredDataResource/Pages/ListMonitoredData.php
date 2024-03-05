<?php

namespace App\Filament\Resources\MonitoredDataResource\Pages;

use App\Filament\Resources\MonitoredDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitoredData extends ListRecords
{
    protected static string $resource = MonitoredDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
