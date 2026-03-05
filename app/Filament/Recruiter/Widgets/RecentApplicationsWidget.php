<?php

namespace App\Filament\Recruiter\Widgets;

use App\Models\Application;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Oxirgi arizalar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Application::query()
                    ->whereHas('vacancy.employer', fn($q) => $q->where('user_id', auth()->id()))
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('vacancy.title')->label('Vakansiya')->limit(25),
                Tables\Columns\TextColumn::make('worker.full_name')->label('Nomzod'),
                Tables\Columns\TextColumn::make('stage')->label('Bosqich')->badge(),
                Tables\Columns\TextColumn::make('questionnaire_score')->label('Ball')->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->label('Sana')->dateTime(),
            ])
            ->paginated(false);
    }
}
