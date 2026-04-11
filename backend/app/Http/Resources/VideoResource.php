<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'duration' => $this->duration,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->profile?->avatar,
            ],

            'stream' => $this->whenLoaded('stream', function () {
                return [
                    'id' => $this->stream?->id,
                    'title' => $this->stream?->title,
                    'status' => $this->stream?->status,
                ];
            }),

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                });
            }),

            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'stream_id' => $comment->stream_id,
                        'video_id' => $comment->video_id,
                        'created_at' => $comment->created_at,
                        'user' => [
                             'id' => $comment->user?->id,
                             'name' => $comment->user?->name,
                             'email' => $comment->user?->email,
                             'avatar' => $comment->user?->profile?->avatar,
                         ],
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'stream' => $this->whenLoaded('stream', function () {
                return [
                    'id' => $this->stream?->id,
                    'title' => $this->stream?->title,
                    'status' => $this->stream?->status,
                    'reactions_count' => $this->stream?->reactions_count,
                ];
            }),
        ];
    }
}
