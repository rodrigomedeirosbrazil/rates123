<?php

namespace App\Filament\Admin\Resources\MonitoredPropertyResource\Pages;

use App\Filament\Admin\Resources\MonitoredPropertyResource;
use App\Filament\Admin\Resources\MonitoredPropertyResource\Widgets\CalendarWidget;
use App\Filament\Admin\Resources\MonitoredPropertyResource\Widgets\MonitoredPropertyPricesOverview;
use App\Filament\Admin\Resources\MonitoredPropertyResource\Widgets\MonitoredPropertySyncOverview;
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
            CalendarWidget::make([
                'property' => $this->record,
            ]),
        ];
    }
}
