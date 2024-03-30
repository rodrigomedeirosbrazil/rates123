<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceNotificationResource\Pages;
use App\Models\MonitoredData;
use App\Models\PriceNotification;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                        ->content(fn ($record) => $record->monitoredProperty->name),
                    Forms\Components\TextInput::make('type'),
                ])->columns(2),

                Grid::make([])->schema([
                    TextInput::make('checkin')
                        ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),
                    TextInput::make('created_at')
                        ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state)),
                ])->columns(2),

                Grid::make([])->schema([
                    TextInput::make('before')
                        ->prefix('$'),

                    TextInput::make('after')
                        ->prefix('$'),

                    TextInput::make('change_percent')
                        ->suffix('%'),
                ])->columns(3),

                Placeholder::make('Price History')
                    ->content(function (?Model $record) {
                        if (! $record) {
                            return 'No record';
                        }

                        return new HtmlString(MonitoredData::query()
                            ->where('monitored_property_id', $record->monitored_property_id)
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
                Tables\Columns\TextColumn::make('monitoredProperty.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_percent'),
                Tables\Columns\TextColumn::make('before'),
                Tables\Columns\TextColumn::make('after'),
                Tables\Columns\TextColumn::make('checkin')
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->searchOnBlur()
            ->filters([

                Filter::make('monitored_property_id')
                    ->form([
                        Select::make('monitored_property_id')
                            ->label('Property')
                            ->searchable(['name'])
                            ->relationship(name: 'monitoredProperty', titleAttribute: 'name'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['monitored_property_id'],
                                fn (Builder $query, $value): Builder => $query->where('monitored_property_id', $value),
                            )
                    ),

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
}
