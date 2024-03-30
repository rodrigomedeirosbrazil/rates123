<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BrasilStatesEnum;
use App\Filament\Admin\Resources\MonitoredPropertyResource\Pages;
use App\Models\MonitoredProperty;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoredPropertyResource extends Resource
{
    protected static ?string $model = MonitoredProperty::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->required(),

                    Select::make('monitored_platform_id')
                        ->label('Platform')
                        ->relationship(name: 'platform', titleAttribute: 'name')
                        ->preload() // ->searchable(['name'])
                        ->required(),

                    TextInput::make('url')
                        ->columnSpan(2)
                        ->required(),
                ])->columns(4),

                Grid::make()->schema([
                    Select::make('country')
                        ->options([
                            'Brasil' => 'Brasil',
                        ])
                        ->selectablePlaceholder(false)
                        ->default('Brasil')
                        ->columnSpan(2),

                    TextInput::make('city')
                        ->columnSpan(3),

                    Select::make('state')
                        ->options(BrasilStatesEnum::toArray())
                        ->default('SP'),

                    TextInput::make('neighborhood')
                        ->columnSpan(2),
                ])->columns(8),

                Grid::make()->schema([
                    TextInput::make('address')
                        ->columnSpan(4),

                    TextInput::make('number'),

                    TextInput::make('complement')
                        ->columnSpan(2),

                    TextInput::make('postal_code'),

                ])->columns(8),




                TextInput::make('latitude'),

                TextInput::make('longitude'),

                Textarea::make('extra')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('platform.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('Not Synced Today')
                    ->query(
                        fn (Builder $query): Builder => $query->whereDoesntHave(
                            'priceDatas',
                            fn (Builder $query) => $query->whereDate('created_at', now())
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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
            'index' => Pages\ListMonitoredProperties::route('/'),
            'create' => Pages\CreateMonitoredProperty::route('/create'),
            'edit' => Pages\EditMonitoredProperty::route('/{record}/edit'),
        ];
    }
}
