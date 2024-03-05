<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoredDataResource\Pages;
use App\Models\MonitoredData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoredDataResource extends Resource
{
    protected static ?string $model = MonitoredData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('monitored_property_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\DatePicker::make('checkin')
                    ->required(),
                Forms\Components\Toggle::make('available')
                    ->required(),
                Forms\Components\Textarea::make('extra')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monitoredProperty.name')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin')
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
                Tables\Columns\IconColumn::make('available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchOnBlur()
            ->filters([
                Filter::make('Available')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(true)),
                Filter::make('Unavailable')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(false)),
            ], layout: FiltersLayout::Modal)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('checkin', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoredData::route('/'),
            'create' => Pages\CreateMonitoredData::route('/create'),
            'edit' => Pages\EditMonitoredData::route('/{record}/edit'),
        ];
    }
}
