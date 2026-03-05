<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ApplicationStage;
use App\Filament\Admin\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Kontentlar';

    protected static ?int $navigationSort = 21;

    protected static ?string $modelLabel = 'Ariza';

    protected static ?string $pluralModelLabel = 'Arizalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vacancy_id')
                    ->label('Vakansiya')
                    ->relationship('vacancy', 'title')
                    ->disabled(),
                Forms\Components\Select::make('worker_id')
                    ->label('Ishchi')
                    ->disabled(),
                Forms\Components\Select::make('stage')
                    ->label('Bosqich')
                    ->options(ApplicationStage::class),
                Forms\Components\TextInput::make('questionnaire_score')
                    ->label('Ball')
                    ->disabled()
                    ->numeric(),
                Forms\Components\TextInput::make('recruiter_rating')
                    ->label('Reyting')
                    ->disabled()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vacancy.title')
                    ->label('Vakansiya')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('worker.full_name')
                    ->label('Nomzod')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Bosqich')
                    ->badge()
                    ->color(fn (ApplicationStage $state) => $state->color()),
                Tables\Columns\TextColumn::make('questionnaire_score')
                    ->label('Ball')
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('knockout_passed')
                    ->label('Knockout')
                    ->boolean(),
                Tables\Columns\TextColumn::make('recruiter_rating')
                    ->label('Reyting')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Manba'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ariza sanasi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Bosqich')
                    ->options(ApplicationStage::class),
                Tables\Filters\TernaryFilter::make('knockout_passed')
                    ->label('Knockout'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListApplications::route('/'),
            'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }
}
