<?php

namespace App\Filament\Shared\Resources\MonitoredPropertyResource\Pages;

use App\Filament\Shared\Resources\MonitoredPropertyResource;
use App\Filament\Shared\Resources\MonitoredPropertyResource\Widgets\CalendarWidget;
use App\Filament\Shared\Resources\MonitoredPropertyResource\Widgets\MonitoredPropertyPricesOverview;
use App\Filament\Shared\Resources\MonitoredPropertyResource\Widgets\MonitoredPropertySyncOverview;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoredProperty extends ViewRecord
{
    protected static string $resource = MonitoredPropertyResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            MonitoredPropertyPricesOverview::class,
            MonitoredPropertySyncOverview::class,
            CalendarWidget::make([
                'property' => $this->record,
            ]),
        ];
    }
}
