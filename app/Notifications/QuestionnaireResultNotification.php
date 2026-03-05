<?php

namespace App\Notifications;

use App\Models\QuestionnaireResponse;
use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class QuestionnaireResultNotification extends Notification
{
    public function __construct(public QuestionnaireResponse $response) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'questionnaire_result',
            'response_id' => $this->response->id,
            'application_id' => $this->response->application_id,
            'total_score' => $this->response->total_score,
            'knockout_passed' => $this->response->knockout_passed,
            'title' => 'Savolnoma natijalari',
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage(): string
    {
        if (!$this->response->knockout_passed) {
            return 'Afsus, siz majburiy savollardan o\'ta olmadingiz';
        }

        $score = round($this->response->total_score, 1);

        if ($score >= 80) {
            return "Ajoyib! Siz {$score}% ball to'pladingiz";
        } elseif ($score >= 60) {
            return "Yaxshi! Siz {$score}% ball to'pladingiz";
        } else {
            return "Siz {$score}% ball to'pladingiz";
        }
    }

    /**
     * Create custom notification for worker (applicant)
     */
    public static function createForWorker(QuestionnaireResponse $response): void
    {
        $score = round($response->total_score, 1);

        if (!$response->knockout_passed) {
            $message = 'Afsus, siz majburiy savollardan o\'ta olmadingiz';
        } elseif ($score >= 80) {
            $message = "Ajoyib! Siz {$score}% ball to'pladingiz";
        } elseif ($score >= 60) {
            $message = "Yaxshi! Siz {$score}% ball to'pladingiz";
        } else {
            $message = "Siz {$score}% ball to'pladingiz";
        }

        NotificationModel::create([
            'user_id' => $response->user_id,
            'type' => 'questionnaire_result',
            'title' => 'Savolnoma natijalari',
            'message' => $message,
            'data' => [
                'response_id' => $response->id,
                'application_id' => $response->application_id,
                'total_score' => $response->total_score,
                'knockout_passed' => $response->knockout_passed,
            ],
        ]);
    }

    /**
     * Create custom notification for employer
     */
    public static function createForEmployer(QuestionnaireResponse $response): void
    {
        $application = $response->application;
        $score = round($response->total_score, 1);

        NotificationModel::create([
            'user_id' => $application->vacancy->employer->user_id,
            'type' => 'questionnaire_completed',
            'title' => 'Nomzod savolnomani to\'ldirdi',
            'message' => "{$application->worker->full_name} savolnomani {$score}% ball bilan to'ldirdi",
            'data' => [
                'response_id' => $response->id,
                'application_id' => $response->application_id,
                'worker_name' => $application->worker->full_name,
                'total_score' => $response->total_score,
                'knockout_passed' => $response->knockout_passed,
            ],
        ]);
    }
}
