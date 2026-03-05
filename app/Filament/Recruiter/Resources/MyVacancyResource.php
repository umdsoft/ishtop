<?php

namespace App\Filament\Recruiter\Resources;

use App\Filament\Recruiter\Resources\MyVacancyResource\Pages;
use App\Enums\VacancyStatus;
use App\Enums\WorkType;
use App\Models\Vacancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyVacancyResource extends Resource
{
    protected static ?string $model = Vacancy::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $modelLabel = 'Vakansiya';
    protected static ?string $pluralModelLabel = 'Vakansiyalarim';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('employer', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Asosiy ma\'lumotlar')->schema([
                Forms\Components\TextInput::make('title')->label('Sarlavha')->required()->maxLength(200),
                Forms\Components\TextInput::make('category')->label('Kategoriya')->required(),
                Forms\Components\Textarea::make('description')->label('Tavsif')->required()->rows(5),
                Forms\Components\Textarea::make('requirements')->label('Talablar')->rows(3),
                Forms\Components\Textarea::make('responsibilities')->label('Vazifalar')->rows(3),
            ]),
            Forms\Components\Section::make('Maosh va ish turi')->schema([
                Forms\Components\TextInput::make('salary_min')->label('Maosh (min)')->numeric(),
                Forms\Components\TextInput::make('salary_max')->label('Maosh (max)')->numeric(),
                Forms\Components\Select::make('salary_type')->label('Maosh turi')->options(['fixed'=>'Belgilangan', 'range'=>'Diapazon', 'negotiable'=>'Kelishiladi']),
                Forms\Components\Select::make('work_type')->label('Ish turi')->options(WorkType::class),
                Forms\Components\TextInput::make('experience_required')->label('Tajriba'),
            ])->columns(2),
            Forms\Components\Section::make('Joylashuv')->schema([
                Forms\Components\TextInput::make('city')->label('Shahar')->required(),
                Forms\Components\TextInput::make('district')->label('Tuman'),
                Forms\Components\TextInput::make('contact_phone')->label('Aloqa telefoni')->tel(),
                Forms\Components\Select::make('contact_method')->label('Aloqa usuli')->options(['telegram'=>'Telegram', 'phone'=>'Telefon', 'both'=>'Ikkalasi']),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Sarlavha')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('category')->label('Kategoriya'),
                Tables\Columns\TextColumn::make('city')->label('Shahar'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (VacancyStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('applications_count')->label('Arizalar')->counts('applications')->sortable(),
                Tables\Columns\TextColumn::make('views_count')->label('Ko\'rishlar')->sortable(),
                Tables\Columns\TextColumn::make('published_at')->label('E\'lon qilingan')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(VacancyStatus::class),
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
            'index' => Pages\ListMyVacancies::route('/'),
            'create' => Pages\CreateMyVacancy::route('/create'),
            'edit' => Pages\EditMyVacancy::route('/{record}/edit'),
            'view' => Pages\ViewMyVacancy::route('/{record}'),
        ];
    }
}
