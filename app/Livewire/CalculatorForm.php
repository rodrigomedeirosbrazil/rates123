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
use Illuminate\Support\Collection;
use Livewire\Component;

class CalculatorForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ?Collection $receiptItems;

    public function __construct()
    {
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->receiptItems = collect();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(__('Season'))->schema([
                    Toggle::make('is_high_season')
                        ->label(__('Is high season'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('high_season_rate')
                        ->label(__('High season rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_high_season')),
                ]),

                Fieldset::make(__('Weekends'))->schema([
                    Toggle::make('is_weekend')
                        ->label(__('Is weekend'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('weekend_rate')
                        ->label(__('Weekend rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_weekend')),
                ]),

                Fieldset::make(__('Holidays'))->schema([
                    Toggle::make('is_holiday')
                        ->label(__('Is holiday'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('holiday_rate')
                        ->label(__('Holiday rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_holiday')),
                ]),

                Fieldset::make(__('Weather'))->schema([
                    Toggle::make('is_bad_weather')
                        ->label(__('Is bad weather'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('bad_weather_rate')
                        ->label(__('Bad weather rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_bad_weather')),
                ]),

                Fieldset::make(__('Events'))->schema([
                    Toggle::make('is_event')
                        ->label(__('Is event'))
                        ->inline(false)
                        ->live(),

                    TextInput::make('event_rate')
                        ->label(__('Event rate (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric()
                        ->hidden(fn (Get $get) => ! $get('is_event')),
                ]),


                Grid::make([])->schema([
                    TextInput::make('min_price')
                        ->label(__('Minimum price'))
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->numeric(),

                    TextInput::make('max_price')
                        ->label(__('Maximum price'))
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->numeric(),
                ])->columns(2),


                Grid::make([])->schema([
                    TextInput::make('occupancy')
                        ->label(__('Occupancy (%)'))
                        ->default(50)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true)
                        ->numeric(),

                    TextInput::make('days_to_arrival')
                        ->label(__('Days to arrival'))
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->numeric(),

                    TextInput::make('competitor_price')
                        ->label(__('Competitor price'))
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
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

        $this->receiptItems = collect([]);

        $minPrice = (float) data_get($data, 'min_price', 0);

        if (data_get($data, 'is_high_season', false)) {
            $subTotal = ($minPrice * (1 + (data_get($data, 'high_season_rate', 0) / 100))) - $minPrice;
            $this->receiptItems->push(
                (object) [
                    'name' => 'High season',
                    'amount' => $subTotal,
                ]
            );
        }

        if (data_get($data, 'is_weekend', false)) {
            $subTotal = ($minPrice * (1 + (data_get($data, 'weekend_rate', 0) / 100))) - $minPrice;
            $this->receiptItems->push(
                (object) [
                    'name' => 'Weekend',
                    'amount' => $subTotal,
                ]
            );
        }

        if (data_get($data, 'is_holiday', false)) {
            $subTotal = ($minPrice * (1 + (data_get($data, 'holiday_rate', 0) / 100))) - $minPrice;
            $this->receiptItems->push(
                (object) [
                    'name' => 'Holiday',
                    'amount' => $subTotal,
                ]
            );
        }

        if (data_get($data, 'is_bad_weather', false)) {
            $subTotal = (($minPrice * (1 + (data_get($data, 'bad_weather_rate', 0) / 100))) - $minPrice) * -1;
            $this->receiptItems->push(
                (object) [
                    'name' => 'Bad weather',
                    'amount' => $subTotal,
                ]
            );
        }

        if (data_get($data, 'is_event', false)) {
            $subTotal = ($minPrice * (1 + (data_get($data, 'event_rate', 0) / 100))) - $minPrice;
            $this->receiptItems->push(
                (object) [
                    'name' => 'Event',
                    'amount' => $subTotal,
                ]
            );
        }

        $this->receiptItems->push(
            (object) [
                'name' => 'Total',
                'amount' => $minPrice + $this->receiptItems->sum('amount'),
            ]
        );

        dump($this->receiptItems->toArray());
    }

    public function render(): View
    {
        return view('livewire.calculator-form');
    }
}
