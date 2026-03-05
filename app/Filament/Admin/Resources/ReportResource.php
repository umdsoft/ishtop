<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Moderatsiya';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Shikoyat';

    protected static ?string $pluralModelLabel = 'Shikoyatlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reason')
                    ->label('Sabab')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->label('Tavsif')
                    ->disabled()
                    ->rows(3),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Ochiq',
                        'reviewing' => 'Ko\'rilmoqda',
                        'resolved' => 'Hal qilingan',
                        'dismissed' => 'Rad etilgan',
                    ]),
                Forms\Components\Textarea::make('admin_note')
                    ->label('Admin izohi')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter.first_name')
                    ->label('Shikoyatchi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reportable_type')
                    ->label('Turi')
                    ->formatStateUsing(fn (string $state) => class_basename($state)),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Sabab')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'danger',
                        'reviewing' => 'warning',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Ochiq',
                        'reviewing' => 'Ko\'rilmoqda',
                        'resolved' => 'Hal qilingan',
                        'dismissed' => 'Rad etilgan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->label('Hal qilish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Report $record) {
                        $record->update([
                            'status' => 'resolved',
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);
                    })
                    ->visible(fn (Report $record) => $record->status !== 'resolved'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
