<?php

namespace App\Filament\Admin\Resources\DateEventResource\Pages;

use App\Filament\Admin\Resources\DateEventResource;
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
