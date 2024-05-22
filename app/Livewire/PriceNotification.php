<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Livewire\Component;
use Filament\Infolists\Infolist;

class PriceNotification extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public array $priceNotification;

    public function mount(array $priceNotification)
    {
        $this->priceNotification = $priceNotification;
    }

    public function render()
    {
        return view('livewire.price-notification');
    }

    public function priceNotificationlist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([
                'propertyId' => data_get($this->priceNotification, 'propertyId'),
                'checkin' => data_get($this->priceNotification, 'checkin'),
                'type' => data_get($this->priceNotification, 'type'),
                'oldPrice' => data_get($this->priceNotification, 'oldPrice'),
                'newPrice' => data_get($this->priceNotification, 'newPrice'),
                'variationToLastPrice' => data_get($this->priceNotification, 'variationToLastPrice'),
                'variationToBasePrice' => data_get($this->priceNotification, 'variationToBasePrice'),
            ])
            ->schema([
                Fieldset::make(
                    format_date_with_weekday(data_get($this->priceNotification, 'checkin'))
                )
                    ->schema([
                        TextEntry::make('propertyId')->label(__('Property')),
                        TextEntry::make('checkin')->label(__('Checkin')),
                        TextEntry::make('type')->label(__('Type')),
                        TextEntry::make('oldPrice')->label(__('Old Price')),
                        TextEntry::make('newPrice')->label(__('New Price')),
                        TextEntry::make('variationToLastPrice')->label(__('Variation to Last Price')),
                        TextEntry::make('variationToBasePrice')->label(__('Variation to Base Price')),
                    ]),
            ]);
    }
}
