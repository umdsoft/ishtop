<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 11;

    protected static ?string $modelLabel = 'Shahar';

    protected static ?string $pluralModelLabel = 'Shaharlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shahar ma\'lumotlari')
                    ->schema([
                        Forms\Components\TextInput::make('name_uz')
                            ->label('Shahar (UZ)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('name_ru')
                            ->label('Shahar (RU)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('region')
                            ->label('Viloyat')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Kenglik')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Uzunlik')
                            ->numeric(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_uz')
                    ->label('Shahar (UZ)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Shahar (RU)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->label('Viloyat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Faol')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Faol'),
                Tables\Filters\SelectFilter::make('region')
                    ->label('Viloyat')
                    ->options(fn (): array => City::query()->distinct()->pluck('region', 'region')->toArray()),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
