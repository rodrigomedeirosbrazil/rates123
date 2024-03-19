<?php

namespace App\Filament\Resources;

use App\Enums\DateEventTypeEnum;
use App\Filament\Resources\DateEventResource\Pages;
use App\Models\DateEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DateEventResource extends Resource
{
    protected static ?string $model = DateEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\DatePicker::make('begin')
                    ->required(),
                Forms\Components\DatePicker::make('end')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options(DateEventTypeEnum::toArray())
                    ->required(),
                Forms\Components\Select::make('country')
                    ->options([
                        'Brasil' => 'Brasil',
                    ])
                    ->selectablePlaceholder(false)
                    ->default('Brasil'),
                Forms\Components\TextInput::make('state'),
                Forms\Components\TextInput::make('city'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('begin')
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->formatStateUsing(fn (string $state): string => format_date_with_weekday($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
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
            'index' => Pages\ManageDateEvents::route('/'),
        ];
    }
}
