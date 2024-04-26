<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\CalendarResource\Pages;
use App\Filament\Shared\Resources\CalendarResource\Widgets\CalendarWidget;
use App\Models\Rate;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class CalendarResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-m-calendar-days';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalendars::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Calendar');
    }
}
