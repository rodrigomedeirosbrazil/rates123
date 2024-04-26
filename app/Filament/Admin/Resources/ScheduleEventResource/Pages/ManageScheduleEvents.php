<?php

namespace App\Filament\Admin\Resources\ScheduleEventResource\Pages;

use App\Filament\Admin\Resources\ScheduleEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageScheduleEvents extends ManageRecords
{
    protected static string $resource = ScheduleEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
