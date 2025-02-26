<?php

namespace App\Filament\Shared\Resources;

use App\Enums\BrasilStatesEnum;
use App\Filament\Shared\Resources\PropertyResource\Pages;
use App\Managers\PriceManager;
use App\Models\Property;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    Placeholder::make('modePrice')
                        ->label(__('Avg Price'))
                        ->columnSpan(1)
                        ->content(
                            fn ($record) => $record?->id !== null
                            ? '$' . number_format(
                                app(PriceManager::class)->calculatePropertyModePrice($record->id),
                                2
                            )
                        : ''
                        ),

                    TextInput::make('name')
                        ->columnSpan(2)
                        ->label(__('Name'))
                        ->required(),

                    Select::make('scraped_platform_id')
                        ->columnSpan(1)
                        ->label(__('Platform'))
                        ->relationship(name: 'platform', titleAttribute: 'name')
                        ->preload() // ->searchable(['name'])
                        ->required(),

                    TextInput::make('url')
                        ->columnSpan(4)
                        ->suffixAction(
                            FormAction::make('openOnPlatform')
                                ->icon('heroicon-m-calendar-days')
                                ->url(fn (?Model $record) => $record?->url, shouldOpenInNewTab: true)
                        )
                        ->required(),
                ])->columns(8),

                Fieldset::make(__('Follow Properties'))->schema([
                    Repeater::make('followProperties')
                        ->label('')
                        ->relationship()
                        ->simple(
                            Select::make('followed_property_id')
                                ->label(__('Property'))
                                ->options(Property::all()->pluck('name', 'id'))
                                ->searchable()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->required(),
                        ),
                ])
                    ->columnSpanFull(),

                Fieldset::make(__('Address'))->schema([

                    Grid::make()->schema([
                        Select::make('country')
                            ->label(__('Country'))
                            ->options([
                                'Brasil' => 'Brasil',
                            ])
                            ->selectablePlaceholder(false)
                            ->default('Brasil')
                            ->columnSpan(2),

                        TextInput::make('city')
                            ->label(__('City'))
                            ->columnSpan(3),

                        Select::make('state')
                            ->label(__('State'))
                            ->options(BrasilStatesEnum::toArray())
                            ->default('SP'),

                        TextInput::make('neighborhood')
                            ->label(__('Neighborhood'))
                            ->columnSpan(2),
                    ])->columns(8),

                    Grid::make()->schema([
                        TextInput::make('address')
                            ->label(__('Address'))
                            ->columnSpan(4),

                        TextInput::make('number')
                            ->label(__('Number')),

                        TextInput::make('complement')
                            ->label(__('Complement'))
                            ->columnSpan(2),

                        TextInput::make('postal_code')
                            ->label(__('Postal Code')),

                    ])->columns(8),

                    TextInput::make('latitude')
                        ->label(__('Latitude')),

                    TextInput::make('longitude')
                        ->label(__('Longitude')),
                ])
                    ->columnSpanFull(),

                TextInput::make('hits_property_id')
                    ->hidden()
                    ->label(__('Hits Property ID')),

                Textarea::make('extra')
                    ->hidden()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),

                TextColumn::make('platform.name')
                    ->label(__('Platform'))
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
                TextColumn::make('deleted_at')
                    ->label(__('Deleted At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('Not Synced Today')
                    ->label('Not Synced Today')
                    ->query(
                        fn (Builder $query): Builder => $query->whereDoesntHave(
                            'priceDatas',
                            fn (Builder $query) => $query->whereDate('created_at', now())
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
            'view' => Pages\ViewProperty::route('/{record}/view'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Property');
    }
}
