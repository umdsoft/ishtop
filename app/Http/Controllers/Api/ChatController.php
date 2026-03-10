<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $chats = Chat::where('worker_user_id', $userId)
            ->orWhere('employer_user_id', $userId)
            ->with([
                'latestMessage',
                'workerUser:id,first_name,last_name,username',
                'employerUser:id,first_name,last_name,username',
                'application.vacancy:id,title_uz,title_ru',
            ])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)
                  ->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return response()->json($chats);
    }

    public function messages(Request $request, string $chat): JsonResponse
    {
        $chatModel = Chat::findOrFail($chat);

        $userId = $request->user()->id;
        if ($chatModel->worker_user_id !== $userId && $chatModel->employer_user_id !== $userId) {
            return response()->json(['message' => 'Ruxsat berilmagan'], 403);
        }

        Message::where('chat_id', $chat)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('chat_id', $chat)
            ->with('sender:id,first_name,last_name')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 50);

        return response()->json($messages);
    }

    public function send(SendMessageRequest $request, string $chat): JsonResponse
    {
        $validated = $request->validated();
        $chatModel = Chat::findOrFail($chat);

        $userId = $request->user()->id;
        if ($chatModel->worker_user_id !== $userId && $chatModel->employer_user_id !== $userId) {
            return response()->json(['message' => 'Ruxsat berilmagan'], 403);
        }

        $message = DB::transaction(function () use ($validated, $chat, $userId, $chatModel) {
            $message = Message::create([
                'chat_id' => $chat,
                'sender_id' => $userId,
                'text' => $validated['text'] ?? null,
                'type' => $validated['type'] ?? 'text',
                'file_url' => $validated['file_url'] ?? null,
            ]);

            $chatModel->update(['last_message_at' => now()]);

            return $message;
        });

        $message->load('sender:id,first_name,last_name');

        return response()->json(['message' => $message], 201);
    }
}
