<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Tizim';

    protected static ?int $navigationSort = 99;

    protected static ?string $modelLabel = 'Sozlama';

    protected static ?string $pluralModelLabel = 'Sozlamalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Kalit')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('value')
                    ->label('Qiymat')
                    ->required()
                    ->rows(2),
                Forms\Components\TextInput::make('group')
                    ->label('Guruh')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Kalit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Qiymat')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('group')
                    ->label('Guruh')
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Guruh'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
