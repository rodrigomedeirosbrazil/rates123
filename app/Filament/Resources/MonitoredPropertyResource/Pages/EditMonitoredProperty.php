<?php

namespace App\Filament\Resources\MonitoredPropertyResource\Pages;

use App\Filament\Resources\MonitoredPropertyResource;
use App\Filament\Resources\MonitoredPropertyResource\Widgets\MonitoredPropertySyncOverview;
use App\Filament\Widgets\MonitoredPropertyPricesOverview;
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
        return [
            MonitoredPropertyPricesOverview::class,
            MonitoredPropertySyncOverview::class,
        ];
    }
}
