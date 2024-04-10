<?php

namespace App\Filament\Shared\Resources\CalendarResource\Pages;

use App\Filament\Shared\Resources\CalendarResource;
use App\Filament\Shared\Resources\CalendarResource\Widgets\CalendarWidget;
use App\Models\MonitoredProperty;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Livewire\Features\SupportEvents\Event;

class ListCalendars extends Page
{
    public ?array $filters = [];

    public ?int $totalBookings = null;

    protected static string $resource = CalendarResource::class;

    protected static string $view = 'filament.calendar-resource.calendar';

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
                            Select::make('monitored_property_id')
                                ->label(__('Property'))
                                ->options(fn () => MonitoredProperty::all()->pluck('name', 'id'))
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
        return $this->dispatch('bookings-filter-changed', [
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
        return ! empty($this->getFilter('monitored_property_id'));
    }

    protected function getFooterWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Calendar');
    }
}
