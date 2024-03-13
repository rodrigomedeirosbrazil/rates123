<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoredSyncResource\Pages;
use App\Models\MonitoredSync;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoredSyncResource extends Resource
{
    protected static ?string $model = MonitoredSync::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('monitored_property_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('successful')
                    ->required(),
                Forms\Components\TextInput::make('prices_count')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('started_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('finished_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monitoredProperty.name')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                Tables\Columns\IconColumn::make('successful')
                    ->boolean(),
                Tables\Columns\TextColumn::make('prices_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('finished_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->searchOnBlur()
            ->filters([
                Filter::make('Successful')
                    ->query(fn (Builder $query): Builder => $query->whereSuccessful(true)),
                Filter::make('Unsuccessful')
                    ->query(fn (Builder $query): Builder => $query->whereSuccessful(false)),

                Filter::make('started_at')
                    ->form([
                        DatePicker::make('started_at'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['started_at'],
                                fn (Builder $query, $date): Builder => $query->whereDate('started_at', '=', $date),
                            )
                    ),
            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('started_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMonitoredSyncs::route('/'),
        ];
    }
}
