<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Enums\Language;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Foydalanuvchilar';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Foydalanuvchi';

    protected static ?string $pluralModelLabel = 'Foydalanuvchilar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shaxsiy ma\'lumotlar')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Ism')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Familiya')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->maxLength(255),
                        Forms\Components\Select::make('language')
                            ->label('Til')
                            ->options(Language::class),
                    ])->columns(2),

                Forms\Components\Section::make('Holat')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Tasdiqlangan'),
                        Forms\Components\Toggle::make('is_blocked')
                            ->label('Bloklangan'),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balans')
                            ->numeric()
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('telegram_id')
                    ->label('Telegram ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Ism')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Familiya')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('Til')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Tasdiqlangan')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Bloklangan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balans')
                    ->money('UZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_active_at')
                    ->label('Oxirgi faollik')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ro\'yxatdan o\'tgan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Tasdiqlangan'),
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Bloklangan'),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Til')
                    ->options(Language::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('toggle_block')
                    ->label('Bloklash/Ochish')
                    ->icon('heroicon-o-lock-closed')
                    ->color(fn (User $record): string => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_blocked' => !$record->is_blocked])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Shaxsiy ma\'lumotlar')
                    ->schema([
                        Infolists\Components\TextEntry::make('telegram_id')
                            ->label('Telegram ID'),
                        Infolists\Components\TextEntry::make('first_name')
                            ->label('Ism'),
                        Infolists\Components\TextEntry::make('last_name')
                            ->label('Familiya'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefon'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('username')
                            ->label('Username'),
                        Infolists\Components\TextEntry::make('language')
                            ->label('Til')
                            ->badge(),
                        Infolists\Components\TextEntry::make('avatar_url')
                            ->label('Avatar URL'),
                        Infolists\Components\TextEntry::make('referral_code')
                            ->label('Referal kod'),
                    ])->columns(3),

                Infolists\Components\Section::make('Holat')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Tasdiqlangan')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_blocked')
                            ->label('Bloklangan')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('balance')
                            ->label('Balans')
                            ->money('UZS'),
                        Infolists\Components\TextEntry::make('last_active_at')
                            ->label('Oxirgi faollik')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Ro\'yxatdan o\'tgan')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
