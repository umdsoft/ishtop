<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $unreadCount = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $notif = Notification::where('id', $notification)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notif->markAsRead();

        return response()->json(['notification' => $notif]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Barcha bildirishnomalar o\'qilgan deb belgilandi',
            'updated' => $count,
        ]);
    }
}
