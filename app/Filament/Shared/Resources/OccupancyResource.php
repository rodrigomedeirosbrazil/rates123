<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\OccupancyResource\Pages;
use App\Models\Occupancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class OccupancyResource extends Resource
{
    protected static ?string $model = Occupancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('property_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('checkin')
                    ->required(),
                Forms\Components\TextInput::make('total_rooms')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('occupied_rooms')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name')
                    ->label(__('Property'))
                    ->sortable(),

                TextColumn::make('checkin')
                    ->label(__('Checkin'))
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),

                TextColumn::make('occupancyPercent')
                    ->formatStateUsing(fn ($state): float => number_format($state, 2))
                    ->label(__('Occupancy') . ' (%)'),

                TextColumn::make('total_rooms')
                    ->numeric()
                    ->label(__('Rooms'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('occupied_rooms')
                    ->numeric()
                    ->label(__('Occupied'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label(__('Updated At'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchOnBlur()
            ->filters([
                Filter::make('property_id')
                    ->form([
                        Select::make('property_id')
                            ->label(__('Property'))
                            ->searchable(['name'])
                            ->relationship(name: 'property', titleAttribute: 'name'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['property_id'],
                                fn (Builder $query, $value): Builder => $query->where('property_id', $value),
                            )
                    ),

                Filter::make('checkin')
                    ->label(__('Checkin'))
                    ->form([
                        DatePicker::make('checkin'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['checkin'],
                                fn (Builder $query, $date): Builder => $query->whereDate('checkin', '=', $date),
                            )
                    ),

            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOccupancies::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Occupancy');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Occupancies');
    }
}
