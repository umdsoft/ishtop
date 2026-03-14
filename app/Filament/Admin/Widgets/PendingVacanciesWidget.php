<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\VacancyStatus;
use App\Jobs\NotifyMatchingWorkersJob;
use App\Models\Vacancy;
use App\Services\TelegramNotificationService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class PendingVacanciesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Moderatsiyaga kutayotgan';

    public static function canView(): bool
    {
        return Cache::remember('admin:pending_count', 60, function () {
            return Vacancy::where('status', 'pending')->count();
        }) > 0;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vacancy::query()
                    ->where('status', 'pending')
                    ->with('employer:id,company_name')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('title_uz')
                    ->label('Sarlavha')
                    ->limit(40),
                Tables\Columns\TextColumn::make('employer.company_name')
                    ->label('Kompaniya')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategoriya'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Shahar'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yuborilgan')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Tasdiqlash')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function (Vacancy $record) {
                        $record->update([
                            'status' => VacancyStatus::ACTIVE,
                            'published_at' => now(),
                        ]);
                        Cache::forget('admin:pending_count');
                        Cache::forget('admin:dashboard:stats');
                        app(TelegramNotificationService::class)->notifyVacancyModerated($record, true);
                        NotifyMatchingWorkersJob::dispatch($record);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rad etish')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function (Vacancy $record) {
                        $record->update(['status' => VacancyStatus::CLOSED]);
                        Cache::forget('admin:pending_count');
                        Cache::forget('admin:dashboard:stats');
                        app(TelegramNotificationService::class)->notifyVacancyModerated($record, false);
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Moderatsiya uchun vakansiya yo\'q')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
