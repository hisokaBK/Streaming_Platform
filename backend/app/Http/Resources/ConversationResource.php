<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'participant' => [
                'id' => $this['participant']->id,
                'name' => $this['participant']->name,
                'email' => $this['participant']->email,
                'avatar' => $this['participant']->profile?->avatar,
            ],

            'last_message' => [
                'id' => $this['last_message']->id,
                'content' => $this['last_message']->content,
                'is_read' => $this['last_message']->is_read,
                'sender_id' => $this['last_message']->sender_id,
                'receiver_id' => $this['last_message']->receiver_id,
                'created_at' => $this['last_message']->created_at,
            ],

            'unread_count' => $this['unread_count'],
        ];
    }
}
