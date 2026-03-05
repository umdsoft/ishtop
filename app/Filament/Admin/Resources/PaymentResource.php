<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Moliya';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'To\'lov';

    protected static ?string $pluralModelLabel = 'To\'lovlar';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Foydalanuvchi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Turi'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Summa')
                    ->money('UZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Usul')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PaymentStatus $state) => match ($state) {
                        PaymentStatus::COMPLETED => 'success',
                        PaymentStatus::PENDING => 'warning',
                        PaymentStatus::FAILED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('external_id')
                    ->label('Tashqi ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('method')
                    ->options(PaymentMethod::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
