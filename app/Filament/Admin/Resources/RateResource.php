<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RateResource\Pages;
use App\Models\Rate;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RateResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make([])->schema([
                    Placeholder::make('property.name')
                        ->label('Property')
                        ->content(
                            fn ($record) => $record->property->name
                        ),
                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\TextInput::make('min_stay')
                        ->numeric(),
                    Forms\Components\Toggle::make('available')
                        ->inline(false),
                ])->columns(4),


                Grid::make([])->schema([
                    Forms\Components\DatePicker::make('checkin'),
                    Forms\Components\DatePicker::make('created_at'),
                ])->columns(2),

                Forms\Components\Textarea::make('extra')
                    ->afterStateHydrated(function ($component, $state) {
                        $component->state(json_encode($state, JSON_PRETTY_PRINT));
                    })
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_stay')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin')
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
                Tables\Columns\IconColumn::make('available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
            ])
            ->searchOnBlur()
            ->filters([

                Filter::make('property_id')
                    ->form([
                        Select::make('property_id')
                            ->label('Property')
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

                Filter::make('Available')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(true)),

                Filter::make('Unavailable')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(false)),

                Filter::make('checkin')
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

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Filter::make('price')
                    ->form([
                        TextInput::make('price_from')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric(),
                        TextInput::make('price_until')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $value): Builder => $query->where('price', '>=', $value),
                            )
                            ->when(
                                $data['price_until'],
                                fn (Builder $query, $value): Builder => $query->where('price', '<=', $value),
                            );
                    }),

            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ManageRates::route('/'),
        ];
    }
}
