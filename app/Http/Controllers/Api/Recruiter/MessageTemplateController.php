<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $templates = MessageTemplate::where('user_id', $request->user()->id)
            ->orWhere('is_system', true)
            ->orderBy('name')
            ->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
            'body_uz' => 'required|string',
            'body_ru' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        $template = MessageTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'custom',
            'body_uz' => $validated['body_uz'],
            'body_ru' => $validated['body_ru'] ?? null,
            'variables' => $validated['variables'] ?? [],
        ]);

        return response()->json(['template' => $template], 201);
    }

    public function send(Request $request, string $template): JsonResponse
    {
        $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'uuid|exists:applications,id',
            'data' => 'nullable|array',
        ]);

        $templateModel = MessageTemplate::findOrFail($template);
        $userId = $request->user()->id;
        $data = $request->data ?? [];
        $sentCount = 0;

        foreach ($request->application_ids as $appId) {
            $application = Application::with('worker.user', 'vacancy')->find($appId);
            if (!$application) {
                continue;
            }

            $workerUserId = $application->worker?->user_id;
            if (!$workerUserId) {
                continue;
            }

            $mergeData = array_merge($data, [
                'worker_name' => $application->worker?->full_name ?? '',
                'vacancy_title' => $application->vacancy?->title ?? '',
            ]);

            $text = $templateModel->render($mergeData);

            $chat = Chat::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'worker_user_id' => $workerUserId,
                    'employer_user_id' => $userId,
                    'last_message_at' => now(),
                ]
            );

            Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $userId,
                'text' => $text,
                'type' => 'text',
            ]);

            $chat->update(['last_message_at' => now()]);
            $sentCount++;
        }

        return response()->json([
            'message' => "Xabar {$sentCount} ta nomzodga yuborildi",
            'sent_count' => $sentCount,
        ]);
    }
}
