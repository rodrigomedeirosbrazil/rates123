<?php

namespace App\Filament\Shared\Resources\CalendarResource\Pages;

use App\Filament\Shared\Resources\CalendarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalendars extends ListRecords
{
    protected static string $resource = CalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
