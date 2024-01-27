<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CalculatorForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function __construct()
    {
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('is_high_season')
                    ->label(__('Is high season')),

                Toggle::make('is_holiday')
                    ->label(__('Is holiday')),

                Toggle::make('is_raining')
                    ->label(__('Is raining')),

                Toggle::make('is_event')
                    ->label(__('Is event')),

                TextInput::make('min_price')
                    ->label(__('Minimum price'))
                    ->default(0)
                    ->minValue(0)
                    ->numeric(),

                TextInput::make('max_price')
                    ->label(__('Maximum price'))
                    ->default(0)
                    ->minValue(0)
                    ->numeric(),

                TextInput::make('occupancy')
                    ->label(__('Occupancy (%)'))
                    ->default(50)
                    ->minValue(0)
                    ->maxValue(100)
                    ->numeric(),

                TextInput::make('days_to_arrival')
                    ->label(__('Days to arrival'))
                    ->default(0)
                    ->minValue(0)
                    ->numeric(),

                TextInput::make('competitor_price')
                    ->label(__('Competitor price'))
                    ->default(0)
                    ->minValue(0)
                    ->numeric(),

            ])
            ->statePath('data');
    }

    public function calc(): void
    {
        $data = $this->form->getState();

        Notification::make()
            ->title('Calculation')
            ->body('Calculation successful.')
            ->success()
            ->send();

        // $this->form->fill();
    }

    public function render(): View
    {
        return view('livewire.calculator-form');
    }
}
