<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $chats->getCollection()->transform(function ($chat) use ($userId) {
            $chat->unread_count = $chat->unreadCountFor($userId);
            return $chat;
        });

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

    public function send(Request $request, string $chat): JsonResponse
    {
        $request->validate([
            'text' => 'required_without:file_url|string|max:2000',
            'type' => 'nullable|in:text,file,image',
            'file_url' => 'nullable|string|max:500',
        ]);

        $chatModel = Chat::findOrFail($chat);

        $userId = $request->user()->id;
        if ($chatModel->worker_user_id !== $userId && $chatModel->employer_user_id !== $userId) {
            return response()->json(['message' => 'Ruxsat berilmagan'], 403);
        }

        $message = Message::create([
            'chat_id' => $chat,
            'sender_id' => $userId,
            'text' => $request->text,
            'type' => $request->type ?? 'text',
            'file_url' => $request->file_url,
        ]);

        $chatModel->update(['last_message_at' => now()]);

        $message->load('sender:id,first_name,last_name');

        return response()->json(['message' => $message], 201);
    }
}
