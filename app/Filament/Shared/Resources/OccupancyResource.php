<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Resources\OccupancyResource\Pages;
use App\Filament\Shared\Resources\OccupancyResource\RelationManagers;
use App\Models\Occupancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OccupancyResource extends Resource
{
    protected static ?string $model = Occupancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('monitored_property_id')
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
                Tables\Columns\TextColumn::make('monitored_property_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_rooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupied_rooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOccupancies::route('/'),
        ];
    }
}
