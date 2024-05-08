<?php

namespace App\Filament\Shared\Resources\RateGraphResource\Pages;

use App\Filament\Shared\Resources\RateGraphResource;
use App\Filament\Shared\Resources\RateGraphResource\Widgets\RatesOverview;
use App\Models\Property;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Livewire\Features\SupportEvents\Event;

class ListRates extends Page
{
    public ?array $filters = [];

    public ?int $totalBookings = null;

    protected static string $resource = RateGraphResource::class;

    protected static string $view = 'filament.rate-graph-resource.rate';

    protected Actions\Action $applyFilterButton;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fieldset::make()
                ->label(__('Filters'))
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('property_id')
                                ->label(__('Property'))
                                ->options(fn () => Property::all()->pluck('name', 'id'))
                                ->multiple()
                                ->searchable(),

                        ])
                        ->columns(3),
                    Actions::make([
                        Actions\Action::make('apply_filters')
                            ->label(__('Apply'))
                            ->button()
                            ->action(fn () => ! empty($form->validate()) && $this->refreshRecords()),
                    ]),
                    TextInput::make('submit_hidden_button')
                        ->type('submit')
                        ->hiddenLabel()
                        ->extraAttributes(['class' => 'invisible']),
                ]),
        ])
            ->extraAttributes([])
            ->statePath('filters')
            ->debounce();
    }

    public function refreshRecords(): Event
    {
        return $this->dispatch('property-filter-changed', [
            ...$this->filters,
            'is_filtered' => $this->isFiltered(),
        ]);
    }

    public function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->filters, $key, $default);
    }

    public function isFiltered(): bool
    {
        return ! empty($this->getFilter('property_id'));
    }

    protected function getFooterWidgets(): array
    {
        return [
            RatesOverview::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Rate Graph');
    }
}
