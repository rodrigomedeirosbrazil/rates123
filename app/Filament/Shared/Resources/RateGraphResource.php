<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\RateGraphResource\Pages;
use App\Filament\Shared\Resources\RateGraphResource\Widgets\RatesOverview;
use App\Models\Rate;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class RateGraphResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRates::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            RatesOverview::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Rate Graph');
    }
}
