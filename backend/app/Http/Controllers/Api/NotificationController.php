<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = Notification::with([
                'actor.profile',
                'stream',
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this notification.',
            ], 403);
        }

        $notification->update([
            'is_read' => true,
        ]);

        $notification->load([
            'actor.profile',
            'stream',
        ]);

        return response()->json([
            'message' => 'Notification marked as read successfully.',
            'data' => new NotificationResource($notification),
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return response()->json([
            'message' => 'All notifications marked as read successfully.',
        ]);
    }
}
