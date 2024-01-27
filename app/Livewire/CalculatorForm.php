<?php

namespace App\Livewire;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Fieldset::make('Season')->schema([
                    Toggle::make('is_high_season')
                        ->label(__('Is high season'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('high_season_rate')
                        ->label(__('High season rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_high_season')),
                ]),

                Fieldset::make('Weekend')->schema([
                    Toggle::make('is_weekend')
                        ->label(__('Is weekend'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('weekend_rate')
                        ->label(__('Weekend rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_weekend')),
                ]),

                Fieldset::make('Holiday')->schema([
                    Toggle::make('is_holiday')
                        ->label(__('Is holiday'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('holiday_rate')
                        ->label(__('Holiday rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_holiday')),
                ]),

                Fieldset::make('Weather')->schema([
                    Toggle::make('is_raining')
                        ->label(__('Is raining'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('raining_rate')
                        ->label(__('Raining rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_raining')),
                ]),

                Fieldset::make('Events')->schema([
                    Toggle::make('is_event')
                        ->label(__('Is event'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('event_rate')
                        ->label(__('Event rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_event')),
                ]),


                Grid::make([])->schema([
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
                ])->columns(2),


                Grid::make([])->schema([
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
                ])->columns(3),
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
