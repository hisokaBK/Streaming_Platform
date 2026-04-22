<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry', 'clap', 'fire'];

        $reactionsSummary = collect($reactionTypes)->mapWithKeys(function ($type) {
            return [
                $type => $this->stream && $this->stream->relationLoaded('reactions')
                    ? $this->stream->reactions->where('type', $type)->count()
                    : 0,
            ];
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'duration' => $this->duration,

            // fields الجداد
            'egress_id' => $this->egress_id,
            'recording_status' => $this->recording_status,
            'size_bytes' => $this->size_bytes,
            'recorded_at' => $this->recorded_at,

            'comments_count' => $this->comments_count ?? 0,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->profile?->avatar,
            ],

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                })->values();
            }, []),

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
                })->values();
            }, []),

            'stream' => $this->whenLoaded('stream', function () use ($reactionsSummary) {
                return [
                    'id' => $this->stream?->id,
                    'title' => $this->stream?->title,
                    'status' => $this->stream?->status,
                    'thumbnail' => $this->stream?->thumbnail,
                    'reactions_count' => $this->stream?->reactions_count ?? 0,
                    'reactions_summary' => $reactionsSummary,
                ];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
