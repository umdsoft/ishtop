<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class ApplicationStageNotification extends Notification
{
    public function __construct(
        public Application $application,
        public string $oldStage,
        public string $newStage
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'application_stage_changed',
            'application_id' => $this->application->id,
            'vacancy_id' => $this->application->vacancy_id,
            'old_stage' => $this->oldStage,
            'new_stage' => $this->newStage,
            'title' => 'Ariza holati o\'zgarди',
            'message' => $this->getStageMessage(),
        ];
    }

    private function getStageMessage(): string
    {
        $stageMessages = [
            'reviewed' => 'Arizangiz ko\'rib chiqildi',
            'shortlisted' => 'Siz tanlanganlar ro\'yxatiga qo\'shildingiz!',
            'interview' => 'Siz intervyuga taklif qilindingiz',
            'offered' => 'Sizga ish taklif qilindi! Tabriklaymiz!',
            'hired' => 'Siz ishga qabul qilindingiz! Tabriklaymiz!',
            'rejected' => 'Afsus, arizangiz rad etildi',
        ];

        return $stageMessages[$this->newStage] ?? "Ariza holati: {$this->newStage}";
    }

    /**
     * Create custom notification in notifications table
     */
    public static function createNotification(Application $application, string $oldStage, string $newStage): void
    {
        $stageMessages = [
            'reviewed' => 'Arizangiz ko\'rib chiqildi',
            'shortlisted' => 'Siz tanlanganlar ro\'yxatiga qo\'shildingiz!',
            'interview' => 'Siz intervyuga taklif qilindingiz',
            'offered' => 'Sizga ish taklif qilindi! Tabriklaymiz!',
            'hired' => 'Siz ishga qabul qilindingiz! Tabriklaymiz!',
            'rejected' => 'Afsus, arizangiz rad etildi',
        ];

        NotificationModel::create([
            'user_id' => $application->worker->user_id,
            'type' => 'application_stage_changed',
            'title' => 'Ariza holati o\'zgardi',
            'message' => $stageMessages[$newStage] ?? "Ariza holati: {$newStage}",
            'data' => [
                'application_id' => $application->id,
                'vacancy_id' => $application->vacancy_id,
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
            ],
        ]);
    }
}
