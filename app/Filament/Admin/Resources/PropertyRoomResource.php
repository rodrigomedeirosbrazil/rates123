<?php

namespace App\Filament\Admin\Resources;

use App\Enums\RoomTypeEnum;
use App\Filament\Admin\Resources\PropertyRoomResource\Pages;
use App\Models\Property;
use App\Models\PropertyRoom;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyRoomResource extends Resource
{
    protected static ?string $model = PropertyRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('property_id')
                    ->label(__('Property'))
                    ->options(fn () => Property::all()->pluck('name', 'id'))
                    ->searchable(),

                TextInput::make('name')
                    ->required(),

                Select::make('type')
                    ->label(__('Type'))
                    ->options(RoomTypeEnum::toArray())
                    ->required(),

                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->required()
                    ->numeric(),

                TextInput::make('percentage')
                    ->label(__('Percentage'))
                    ->required()
                    ->default(0)
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

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percentage')
                    ->label(__('Percentage'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
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
            'index' => Pages\ManagePropertyRooms::route('/'),
        ];
    }
}
