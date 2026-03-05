<?php

namespace App\Filament\Admin\Resources;

use App\Enums\SubscriptionPlan;
use App\Filament\Admin\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Moliya';

    protected static ?int $navigationSort = 31;

    protected static ?string $modelLabel = 'Obuna';

    protected static ?string $pluralModelLabel = 'Obunalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Foydalanuvchi')
                    ->relationship('user', 'first_name')
                    ->required(),
                Forms\Components\Select::make('plan')
                    ->label('Tarif')
                    ->options(SubscriptionPlan::class)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Faol',
                        'expired' => 'Tugagan',
                        'cancelled' => 'Bekor',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Narx')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Boshlanish'),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Tugash'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Foydalanuvchi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan')
                    ->label('Tarif')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Narx')
                    ->money('UZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Boshlanish')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Tugash')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->options(SubscriptionPlan::class),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Faol',
                        'expired' => 'Tugagan',
                        'cancelled' => 'Bekor',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
