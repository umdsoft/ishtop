<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployerProfileResource\Pages;
use App\Models\EmployerProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployerProfileResource extends Resource
{
    protected static ?string $model = EmployerProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Foydalanuvchilar';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Ish beruvchi profili';
    protected static ?string $pluralModelLabel = 'Ish beruvchi profillari';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kompaniya ma\'lumotlari')->schema([
                Forms\Components\TextInput::make('company_name')
                    ->label('Kompaniya nomi')
                    ->required()
                    ->maxLength(200),
                Forms\Components\Textarea::make('company_description')
                    ->label('Kompaniya haqida')
                    ->rows(3),
                Forms\Components\TextInput::make('industry')
                    ->label('Soha'),
                Forms\Components\TextInput::make('company_size')
                    ->label('Xodimlar soni'),
                Forms\Components\TextInput::make('inn')
                    ->label('INN'),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefon')
                    ->tel(),
                Forms\Components\TextInput::make('website')
                    ->label('Veb-sayt')
                    ->url(),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Tasdiqlangan'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Kompaniya')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Egasi'),
                Tables\Columns\TextColumn::make('industry')
                    ->label('Soha')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_size')
                    ->label('Xodimlar soni'),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Reyting')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Tasdiqlangan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmployerProfiles::route('/'),
            'view' => Pages\ViewEmployerProfile::route('/{record}'),
            'edit' => Pages\EditEmployerProfile::route('/{record}/edit'),
        ];
    }
}
