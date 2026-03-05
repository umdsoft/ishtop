<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Vacancy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVacanciesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Oxirgi vakansiyalar';

    public function table(Table $table): Table
    {
        return $table
            ->query(Vacancy::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Sarlavha')->limit(30),
                Tables\Columns\TextColumn::make('employer.company_name')->label('Kompaniya'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Sana')->dateTime()->sortable(),
            ])
            ->paginated(false);
    }
}
