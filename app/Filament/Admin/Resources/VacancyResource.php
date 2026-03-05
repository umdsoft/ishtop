<?php

namespace App\Filament\Admin\Resources;

use App\Enums\VacancyStatus;
use App\Enums\WorkType;
use App\Filament\Admin\Resources\VacancyResource\Pages;
use App\Models\Vacancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VacancyResource extends Resource
{
    protected static ?string $model = Vacancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Kontentlar';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Vakansiya';

    protected static ?string $pluralModelLabel = 'Vakansiyalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asosiy ma\'lumotlar')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Sarlavha')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\TextInput::make('category')
                            ->label('Kategoriya')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Tavsif')
                            ->required()
                            ->rows(5),
                        Forms\Components\Textarea::make('requirements')
                            ->label('Talablar')
                            ->rows(3),
                        Forms\Components\Textarea::make('responsibilities')
                            ->label('Mas\'uliyatlar')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Maosh va Ish turi')
                    ->schema([
                        Forms\Components\TextInput::make('salary_min')
                            ->label('Maosh (min)')
                            ->numeric(),
                        Forms\Components\TextInput::make('salary_max')
                            ->label('Maosh (max)')
                            ->numeric(),
                        Forms\Components\Select::make('salary_type')
                            ->label('Maosh turi')
                            ->options([
                                'fixed' => 'Belgilangan',
                                'range' => 'Diapazon',
                                'negotiable' => 'Kelishiladi',
                            ]),
                        Forms\Components\Select::make('work_type')
                            ->label('Ish turi')
                            ->options(WorkType::class),
                        Forms\Components\TextInput::make('experience_required')
                            ->label('Talab qilinadigan tajriba'),
                    ]),

                Forms\Components\Section::make('Joylashuv')
                    ->schema([
                        Forms\Components\TextInput::make('city')
                            ->label('Shahar')
                            ->required(),
                        Forms\Components\TextInput::make('district')
                            ->label('Tuman'),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Aloqa telefoni')
                            ->tel(),
                        Forms\Components\Select::make('contact_method')
                            ->label('Aloqa usuli')
                            ->options([
                                'telegram' => 'Telegram',
                                'phone' => 'Telefon',
                                'both' => 'Ikkalasi',
                            ]),
                    ]),

                Forms\Components\Section::make('Moderatsiya')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(VacancyStatus::class),
                        Forms\Components\Toggle::make('is_top')
                            ->label('TOP'),
                        Forms\Components\Toggle::make('is_urgent')
                            ->label('Shoshilinch'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('E\'lon qilingan'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Muddati'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('employer.company_name')
                    ->label('Kompaniya')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategoriya')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Shahar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('work_type')
                    ->label('Ish turi')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (VacancyStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('salary_min')
                    ->label('Maosh (min)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('salary_max')
                    ->label('Maosh (max)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_top')
                    ->label('TOP')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_urgent')
                    ->label('Shoshilinch')
                    ->boolean(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Arizalar')
                    ->counts('applications')
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Ko\'rishlar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('E\'lon qilingan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Muddati')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(VacancyStatus::class),
                Tables\Filters\SelectFilter::make('work_type')
                    ->label('Ish turi')
                    ->options(WorkType::class),
                Tables\Filters\TernaryFilter::make('is_top')
                    ->label('TOP'),
                Tables\Filters\TernaryFilter::make('is_urgent')
                    ->label('Shoshilinch'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Tasdiqlash')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (Vacancy $record): bool => $record->status === VacancyStatus::PENDING)
                    ->action(function (Vacancy $record): void {
                        $record->update([
                            'status' => VacancyStatus::ACTIVE,
                            'published_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rad etish')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (Vacancy $record): bool => $record->status === VacancyStatus::PENDING)
                    ->action(function (Vacancy $record): void {
                        $record->update([
                            'status' => VacancyStatus::CLOSED,
                        ]);
                    }),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVacancies::route('/'),
            'create' => Pages\CreateVacancy::route('/create'),
            'view' => Pages\ViewVacancy::route('/{record}'),
            'edit' => Pages\EditVacancy::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
