<?php

namespace App\Filament\App\Pages;

use App\Enums\PriceNotificationTypeEnum;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.dashboard';

    public function getPriceNotifications()
    {
        return collect([
            [
                'propertyId' => '1',
                'checkin' => '2021-10-01',
                'type' => PriceNotificationTypeEnum::PriceDown,
                'oldPrice' => '100',
                'newPrice' => '90',
                'variationToLastPrice' => '-10',
                'variationToBasePrice' => '-10',
            ],
            [
                'propertyId' => '2',
                'checkin' => '2021-10-01',
                'type' => PriceNotificationTypeEnum::PriceUp,
                'oldPrice' => '100',
                'newPrice' => '110',
                'variationToLastPrice' => '10',
                'variationToBasePrice' => '10',
            ],
        ]);
    }
}
