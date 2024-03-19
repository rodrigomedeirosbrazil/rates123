<?php

namespace App\Filament\Resources\DateEventResource\Pages;

use App\Filament\Resources\DateEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDateEvents extends ManageRecords
{
    protected static string $resource = DateEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
