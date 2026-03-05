<?php

namespace App\Notifications;

use App\Models\Vacancy;
use App\Models\WorkerProfile;
use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class MatchingNotification extends Notification
{
    public function __construct(
        public Vacancy $vacancy,
        public float $matchScore
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'matching_vacancy',
            'vacancy_id' => $this->vacancy->id,
            'match_score' => $this->matchScore,
            'title' => 'Sizga mos vakansiya topildi',
            'message' => "\"{$this->vacancy->title}\" vakansiyasi sizga {$this->matchScore}% mos keladi",
        ];
    }

    /**
     * Create custom notification in notifications table for worker
     */
    public static function createForWorker(WorkerProfile $worker, Vacancy $vacancy, float $matchScore): void
    {
        NotificationModel::create([
            'user_id' => $worker->user_id,
            'type' => 'matching_vacancy',
            'title' => 'Sizga mos vakansiya topildi',
            'message' => "\"{$vacancy->title}\" vakansiyasi sizga {$matchScore}% mos keladi",
            'data' => [
                'vacancy_id' => $vacancy->id,
                'match_score' => $matchScore,
                'vacancy_title' => $vacancy->title,
                'employer_name' => $vacancy->employer->company_name,
            ],
        ]);
    }

    /**
     * Create custom notification in notifications table for employer
     */
    public static function createForEmployer(Vacancy $vacancy, WorkerProfile $worker, float $matchScore): void
    {
        NotificationModel::create([
            'user_id' => $vacancy->employer->user_id,
            'type' => 'matching_worker',
            'title' => 'Sizga mos nomzod topildi',
            'message' => "{$worker->full_name} \"{$vacancy->title}\" vakansiyangizga {$matchScore}% mos keladi",
            'data' => [
                'worker_id' => $worker->id,
                'vacancy_id' => $vacancy->id,
                'match_score' => $matchScore,
                'worker_name' => $worker->full_name,
                'specialty' => $worker->specialty,
            ],
        ]);
    }
}
