<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoredDataResource\Pages;
use App\Models\MonitoredData;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
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

                Grid::make([])->schema([
                    Placeholder::make('monitoredProperty.name')
                        ->label('Property')
                        ->content(
                            fn ($record) => $record->monitoredProperty->name
                        ),
                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\Toggle::make('available')
                        ->inline(false),
                ])->columns(3),


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
                    ->sortable(),
            ])
            ->searchOnBlur()
            ->filters([
                Filter::make('Available')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(true)),

                Filter::make('Unavailable')
                    ->query(fn (Builder $query): Builder => $query->whereAvailable(false)),

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

            ], layout: FiltersLayout::Modal)
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
            'index' => Pages\ManageMonitoredDatas::route('/'),
        ];
    }
}
