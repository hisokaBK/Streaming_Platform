<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Requests\Message\StoreMessageRequest;

class MessageController extends Controller
{
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $senderId = auth()->id();
        $receiverId = $request->receiver_id;

        if ($senderId === $receiverId) {
            return response()->json([
                'message' => 'You cannot send a message to yourself.',
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'is_read' => false,
        ]);

        $message->load([
            'sender.profile',
            'receiver.profile',
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MessageResource($message),
        ], 201);
    }
    
    public function conversations() : JsonResponse
    {
        $authUserId = auth()->id();

        $messages = Message::with([
                'sender.profile',
                'receiver.profile',
            ])
            ->where(function ($query) use ($authUserId) {
                $query->where('sender_id', $authUserId)
                      ->orWhere('receiver_id', $authUserId);
            })
            ->latest()
            ->get();

        $grouped = $messages->groupBy(function ($message) use ($authUserId) {
            return $message->sender_id === $authUserId
                ? $message->receiver_id
                : $message->sender_id;
        });

        $conversations = $grouped->map(function ($conversationMessages) use ($authUserId) {
            $lastMessage = $conversationMessages->first();

            $participant = $lastMessage->sender_id === $authUserId
                ? $lastMessage->receiver
                : $lastMessage->sender;

            $unreadCount = $conversationMessages
                ->where('receiver_id', $authUserId)
                ->where('is_read', false)
                ->count();

            return [
                'participant' => $participant,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
            ];
        })->values();

        return response()->json([
            'message' => 'Conversations retrieved successfully.',
            'data' => ConversationResource::collection($conversations),
        ]);
    }
}
