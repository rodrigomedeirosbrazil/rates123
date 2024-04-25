<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\PriceNotificationResource\Pages;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class PriceNotificationResource extends Resource
{
    protected static ?string $model = PriceNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([])->schema([
                    Placeholder::make('Property')
                        ->label(__('Property'))
                        ->content(fn ($record) => $record->monitoredProperty->name),

                    TextInput::make('type')
                        ->label(__('Type'))
                        ->formatStateUsing(fn ($state): string => __($state)),
                ])->columns(2),

                Grid::make([])->schema([
                    TextInput::make('checkin')
                        ->label(__('Checkin'))
                        ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),
                    TextInput::make('created_at')
                        ->label(__('Created At'))
                        ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),
                ])->columns(2),

                Grid::make([])->schema([
                    TextInput::make('before')
                        ->label(__('Before'))
                        ->prefix('$'),

                    TextInput::make('after')
                        ->label(__('After'))
                        ->prefix('$'),

                    Placeholder::make('averagePrice')
                        ->label(__('Avg Price'))
                        ->content(fn ($record) => '$' . number_format($record->average_price, 2)),

                    Placeholder::make('Variation')
                        ->label(__('Variation'))
                        ->content(fn ($record) => number_format($record->variation, 2) . '%'),

                    Placeholder::make('averageVariation')
                        ->label(__('Avg Variation'))
                        ->content(fn ($record) => number_format($record->averageVariation, 2) . '%'),

                ])->columns(5),

                Placeholder::make('Price History')
                    ->label(__('Price History'))
                    ->content(function (?Model $record) {
                        if (! $record) {
                            return __('No record');
                        }

                        return new HtmlString(MonitoredData::query()
                            ->where('property_id', $record->property_id)
                            ->where('checkin', $record->checkin)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('created_at', 'desc')
                            ->groupBy('price')
                            ->limit(10)
                            ->get()
                            ->map(function (MonitoredData $data): string {
                                return "{$data->created_at->translatedFormat('l, d F y')} - $ {$data->price}";
                            })
                            ->join('<br>'));
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monitoredProperty.name')
                    ->label(__('Property'))
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->formatStateUsing(fn ($state): string => __($state->value))
                    ->sortable(),

                TextColumn::make('variation')
                    ->formatStateUsing(fn ($state): float => number_format($state, 2))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Variation') . ' (%)'),

                TextColumn::make('averageVariation')
                    ->formatStateUsing(fn ($state): float => number_format($state, 2))
                    ->label(__('Avg Variation') . ' (%)'),

                TextColumn::make('before')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Before')),

                TextColumn::make('after')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('After')),

                TextColumn::make('average_price')
                    ->formatStateUsing(fn ($state): float => number_format($state, 2))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Avg Price') . ' (%)'),

                TextColumn::make('checkin')
                    ->label(__('Checkin'))
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->searchOnBlur()
            ->filters([

                Filter::make('property_id')
                    ->form([
                        Select::make('property_id')
                            ->label(__('Property'))
                            ->searchable(['name'])
                            ->relationship(name: 'monitoredProperty', titleAttribute: 'name'),
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
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePriceNotifications::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Price Notification');
    }
}
