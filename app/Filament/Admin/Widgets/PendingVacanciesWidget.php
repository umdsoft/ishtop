<?php

namespace App\Filament\Admin\Widgets;

use App\Jobs\NotifyMatchingWorkersJob;
use App\Models\Vacancy;
use App\Services\TelegramNotificationService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingVacanciesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Moderatsiyaga kutayotgan';

    public function table(Table $table): Table
    {
        return $table
            ->query(Vacancy::query()->where('status', 'pending')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('title_uz')->label('Sarlavha')->limit(30),
                Tables\Columns\TextColumn::make('employer.company_name')->label('Kompaniya'),
                Tables\Columns\TextColumn::make('category')->label('Kategoriya'),
                Tables\Columns\TextColumn::make('created_at')->label('Sana')->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Tasdiqlash')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (Vacancy $record) {
                        $record->update(['status' => 'active', 'published_at' => now()]);
                        app(TelegramNotificationService::class)->notifyVacancyModerated($record, true);
                        NotifyMatchingWorkersJob::dispatch($record);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rad etish')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(function (Vacancy $record) {
                        $record->update(['status' => 'closed']);
                        app(TelegramNotificationService::class)->notifyVacancyModerated($record, false);
                    }),
            ])
            ->paginated(false);
    }
}
