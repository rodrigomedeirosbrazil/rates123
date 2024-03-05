<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Pages;

use App\Filament\Resources\MonitoredPropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitoredProperties extends ListRecords
{
    protected static string $resource = MonitoredPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
