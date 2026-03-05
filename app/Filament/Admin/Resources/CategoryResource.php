<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Kategoriya';

    protected static ?string $pluralModelLabel = 'Kategoriyalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kategoriya ma\'lumotlari')
                    ->schema([
                        Forms\Components\TextInput::make('name_uz')
                            ->label('Nomi (UZ)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('name_ru')
                            ->label('Nomi (RU)')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('icon')
                            ->label('Ikonka'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Tartib')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_uz')
                    ->label('Nomi (UZ)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Nomi (RU)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Faol')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Tartib')
                    ->sortable(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
