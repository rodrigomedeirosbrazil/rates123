<?php

namespace App\Livewire;

use Filament\Forms\Components\DatePicker;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('begin')
                    ->label(__('Begin Date'))
                    ->required(),
                DatePicker::make('end')
                    ->label(__('End Date'))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function calc(): void
    {
        $data = $this->form->getState();

        Notification::make()
            ->title('Calculation')
            ->message('Calculation successful.')
            ->seconds(5)
            ->send();

        // $this->form->fill();
    }

    public function render(): View
    {
        return view('livewire.calculator-form');
    }
}
