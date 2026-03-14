<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVacanciesWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Oxirgi vakansiyalar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vacancy::query()
                    ->with('employer:id,company_name')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('title_uz')
                    ->label('Sarlavha')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('employer.company_name')
                    ->label('Kompaniya')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategoriya')
                    ->limit(20),
                Tables\Columns\TextColumn::make('city')
                    ->label('Shahar')
                    ->limit(20),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (VacancyStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Ko\'rishlar')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sana')
                    ->since(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc');
    }
}
