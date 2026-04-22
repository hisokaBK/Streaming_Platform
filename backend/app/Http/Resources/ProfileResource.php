<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'background_image' => $this->background_image,
            'bio' => $this->bio,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'role' => $this->user?->role,
                'followers_count' => $this->user?->follower_subscriptions_count ?? 0,
                'following_count' => $this->user?->following_subscriptions_count ?? 0,
            ],

            'followers_preview' => collect($this->followers_preview ?? [])->map(function ($subscription) {
                $follower = $subscription->subscriber;

                return [
                    'id' => $follower?->id,
                    'name' => $follower?->name,
                    'email' => $follower?->email,
                    'avatar' => $follower?->profile?->avatar,
                ];
            })->values(),

            'following_preview' => collect($this->following_preview ?? [])->map(function ($subscription) {
                $streamer = $subscription->streamer;

                return [
                    'id' => $streamer?->id,
                    'name' => $streamer?->name,
                    'email' => $streamer?->email,
                    'avatar' => $streamer?->profile?->avatar,
                ];
            })->values(),

            'videos_preview' => collect($this->videos_preview ?? [])->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'url' => $video->url,
                    'duration' => $video->duration,
                    'egress_id' => $video->egress_id,
                    'recording_status' => $video->recording_status,
                    'size_bytes' => $video->size_bytes,
                    'recorded_at' => $video->recorded_at,
                    'comments_count' => $video->comments_count ?? 0,

                    'thumbnail' => $video->stream?->thumbnail,
                    'thumbnail_url' => $video->stream?->thumbnail,

                    'categories' => collect($video->categories ?? [])->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                        ];
                    })->values(),

                    'stream' => $video->stream ? [
                        'id' => $video->stream->id,
                        'title' => $video->stream->title,
                        'status' => $video->stream->status,
                        'thumbnail' => $video->stream->thumbnail,
                        'thumbnail_url' => $video->stream->thumbnail,
                    ] : null,

                    'created_at' => $video->created_at,
                ];
            })->values(),

            'live_stream' => $this->live_stream ? [
                'id' => $this->live_stream->id,
                'title' => $this->live_stream->title,
                'description' => $this->live_stream->description,
                'status' => $this->live_stream->status,
                'stream_key' => $this->live_stream->stream_key,
                'thumbnail' => $this->live_stream->thumbnail,
                'thumbnail_url' => $this->live_stream->thumbnail,
                'started_at' => $this->live_stream->started_at,
                'ended_at' => $this->live_stream->ended_at,
                'current_viewers' => $this->live_stream->current_viewers,
                'comments_count' => $this->live_stream->comments_count ?? 0,
                'reactions_count' => $this->live_stream->reactions_count ?? 0,
            ] : null,

            'streams_count' => $this->streams_count ?? 0,
            'videos_count' => $this->videos_count ?? 0,

            'most_reacted_video' => $this->most_reacted_video ? [
                'id' => $this->most_reacted_video->id,
                'title' => $this->most_reacted_video->title,
                'description' => $this->most_reacted_video->description,
                'url' => $this->most_reacted_video->url,
                'duration' => $this->most_reacted_video->duration,
                'comments_count' => $this->most_reacted_video->comments_count ?? 0,
                'thumbnail' => $this->most_reacted_video->stream?->thumbnail,
                'thumbnail_url' => $this->most_reacted_video->stream?->thumbnail,
                'reactions_count' => $this->most_reacted_video->stream?->reactions_count ?? 0,
                'created_at' => $this->most_reacted_video->created_at,
            ] : null,

            'created_at' => $this->created_at,
        ];
    }
}
