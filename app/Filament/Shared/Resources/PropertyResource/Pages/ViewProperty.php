<?php

namespace App\Filament\Shared\Resources\PropertyResource\Pages;

use App\Filament\Shared\Resources\PropertyResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
