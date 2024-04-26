<?php

namespace App\Filament\Admin\Resources;

use App\Enums\SyncStatusEnum;
use App\Filament\Admin\Resources\SyncResource\Pages;
use App\Models\Sync;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SyncResource extends Resource
{
    protected static ?string $model = Sync::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.name')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status'),

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
                    ->query(fn (Builder $query): Builder => $query->where('status', SyncStatusEnum::Successful->value)),
                Filter::make('Failed')
                    ->query(fn (Builder $query): Builder => $query->where('status', SyncStatusEnum::Failed->value)),
                Filter::make('InProgress')
                    ->query(fn (Builder $query): Builder => $query->where('status', SyncStatusEnum::InProgress->value)),

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
            ->defaultSort('started_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSyncs::route('/'),
        ];
    }
}
