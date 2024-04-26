<?php

namespace App\Filament\Admin\Resources\ScrapedPlatformResource\Pages;

use App\Filament\Admin\Resources\ScrapedPlatformResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageScrapedPlatforms extends ManageRecords
{
    protected static string $resource = ScrapedPlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
