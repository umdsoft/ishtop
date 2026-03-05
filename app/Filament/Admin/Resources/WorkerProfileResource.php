<?php

namespace App\Filament\Admin\Resources;

use App\Enums\SearchStatus;
use App\Filament\Admin\Resources\WorkerProfileResource\Pages;
use App\Models\WorkerProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerProfileResource extends Resource
{
    protected static ?string $model = WorkerProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Foydalanuvchilar';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Ishchi profili';

    protected static ?string $pluralModelLabel = 'Ishchi profillari';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('F.I.O.')
                    ->disabled(),
                Forms\Components\TextInput::make('specialty')
                    ->label('Mutaxassislik')
                    ->disabled(),
                Forms\Components\TextInput::make('city')
                    ->label('Shahar')
                    ->disabled(),
                Forms\Components\Select::make('search_status')
                    ->label('Qidiruv statusi')
                    ->options(SearchStatus::class),
                Forms\Components\TextInput::make('experience_years')
                    ->label('Tajriba (yil)')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Textarea::make('bio')
                    ->label('Bio')
                    ->disabled()
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('F.I.O.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Telefon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Shahar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('Mutaxassislik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('experience_years')
                    ->label('Tajriba')
                    ->suffix(' yil')
                    ->sortable(),
                Tables\Columns\TextColumn::make('search_status')
                    ->label('Qidiruv statusi')
                    ->badge(),
                Tables\Columns\TextColumn::make('expected_salary_min')
                    ->label('Maosh (min)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Ko\'rishlar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('search_status')
                    ->label('Qidiruv statusi')
                    ->options(SearchStatus::class),
                Tables\Filters\SelectFilter::make('city')
                    ->label('Shahar'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkerProfiles::route('/'),
            'view' => Pages\ViewWorkerProfile::route('/{record}'),
            'edit' => Pages\EditWorkerProfile::route('/{record}/edit'),
        ];
    }
}
