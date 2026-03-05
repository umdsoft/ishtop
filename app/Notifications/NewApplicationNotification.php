<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class NewApplicationNotification extends Notification
{
    public function __construct(public Application $application) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'new_application',
            'application_id' => $this->application->id,
            'vacancy_id' => $this->application->vacancy_id,
            'worker_id' => $this->application->worker_id,
            'title' => 'Yangi ariza',
            'message' => "{$this->application->worker->full_name} \"{$this->application->vacancy->title}\" vakansiyasiga ariza yubordi",
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'vacancy_title' => $this->application->vacancy->title,
            'worker_name' => $this->application->worker->full_name,
        ];
    }

    /**
     * Create custom notification in notifications table
     */
    public static function createNotification(Application $application): void
    {
        NotificationModel::create([
            'user_id' => $application->vacancy->employer->user_id,
            'type' => 'new_application',
            'title' => 'Yangi ariza',
            'message' => "{$application->worker->full_name} \"{$application->vacancy->title}\" vakansiyasiga ariza yubordi",
            'data' => [
                'application_id' => $application->id,
                'vacancy_id' => $application->vacancy_id,
            ],
        ]);
    }
}
