<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $actorName = $this->actor?->name ?? 'Someone';
        $streamTitle = $this->stream?->title ?? null;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->type === 'stream_live'
                ? $actorName . ' is live now'
                : 'Notification',
            'content' => $streamTitle
                ? 'Started a new live stream: ' . $streamTitle
                : $this->content,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,

            'actor' => [
                'id' => $this->actor?->id,
                'name' => $this->actor?->name,
                'email' => $this->actor?->email,
                'avatar' => $this->actor?->profile?->avatar,
            ],

            'stream' => $this->stream ? [
                'id' => $this->stream->id,
                'title' => $this->stream->title,
                'status' => $this->stream->status,
            ] : null,
        ];
    }
}
