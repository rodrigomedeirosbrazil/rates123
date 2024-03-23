<?php

namespace App\Filament\Widgets;

use App\Models\PriceNotification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class CalendarWidget extends FullCalendarWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?Model $property = null;

    public Model|string|null $model = PriceNotification::class;

    public function fetchEvents(array $fetchInfo): array
    {
        $notifications = PriceNotification::query()
            ->where('monitored_property_id', $this->property->id)
            ->where('checkin', '>=', $fetchInfo['start'])
            ->where('checkin', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (PriceNotification $notification) => EventData::make()
                    ->id($notification->id)
                    ->title($notification->type->value)
                    ->start($notification->checkin)
                    ->end($notification->checkin)
            );


        return $notifications->toArray();
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->columns([
                    'sm' => 3,
                    'xl' => 6,
                    '2xl' => 8,
                ])
                ->schema([
                    TextInput::make('type'),
                    DatePicker::make('checkin'),
                    DatePicker::make('created_at'),
                    Textarea::make('message')
                        ->rows(10)
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function headerActions(): array
    {
        return [];
    }
}
