<?php

namespace App\Filament\Shared\Resources\PropertyResource\Pages;

use App\Filament\Shared\Resources\PropertyResource;
use App\Filament\Shared\Resources\PropertyResource\Widgets\PropertyPricesOverview;
use App\Filament\Shared\Resources\PropertyResource\Widgets\PropertySyncOverview;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PropertyPricesOverview::class,
            PropertySyncOverview::class,
        ];
    }
}
