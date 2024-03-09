<?php

namespace App\Filament\Resources\PriceNotificationResource\Pages;

use App\Filament\Resources\PriceNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePriceNotifications extends ManageRecords
{
    protected static string $resource = PriceNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
