<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceNotificationResource\Pages;
use App\Models\PriceNotification;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceNotificationResource extends Resource
{
    protected static ?string $model = PriceNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('Property')
                    ->content(fn ($record) => $record->monitoredProperty->name),
                Forms\Components\TextInput::make('type'),
                Forms\Components\DatePicker::make('checkin'),
                Forms\Components\DatePicker::make('created_at'),
                Forms\Components\Textarea::make('message')
                    ->rows(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monitoredProperty.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePriceNotifications::route('/'),
        ];
    }
}
