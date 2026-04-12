<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_read' => $this->is_read,

            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
                'email' => $this->sender?->email,
                'avatar' => $this->sender?->profile?->avatar,
            ],

            'receiver' => [
                'id' => $this->receiver?->id,
                'name' => $this->receiver?->name,
                'email' => $this->receiver?->email,
                'avatar' => $this->receiver?->profile?->avatar,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
