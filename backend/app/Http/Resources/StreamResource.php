<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StreamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry', 'clap','fire'];

        $reactionsSummary = collect($reactionTypes)->mapWithKeys(function ($type) {
            return [
                $type => $this->whenLoaded('reactions', function () use ($type) {
                    return $this->reactions->where('type', $type)->count();
                }, 0),
            ];
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'stream_key' => $this->stream_key,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'current_viewers' => $this->current_viewers,

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
                });
            }),

            'comments_count' => $this->whenCounted('comments'),
            'reactions_count' => $this->whenCounted('reactions'),
            'reactions_summary' => $reactionsSummary,

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

            'reactions' => $this->whenLoaded('reactions', function () {
                return $this->reactions->map(function ($reaction) {
                    return [
                        'id' => $reaction->id,
                        'type' => $reaction->type,
                        'user_id' => $reaction->user_id,
                        'stream_id' => $reaction->stream_id,
                        'created_at' => $reaction->created_at,
                        'user' => [
                            'id' => $reaction->user?->id,
                            'name' => $reaction->user?->name,
                            'email' => $reaction->user?->email,
                            'avatar' => $reaction->user?->profile?->avatar,
                        ],
                    ];
                });
            }),

            'created_at' => $this->created_at,
        ];
    }
}
