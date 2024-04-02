<?php

namespace App\Filament\Shared\Resources\MonitoredPropertyResource\Pages;

use App\Filament\Shared\Resources\MonitoredPropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonitoredProperty extends EditRecord
{
    protected static string $resource = MonitoredPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
