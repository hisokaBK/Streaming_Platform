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
}
