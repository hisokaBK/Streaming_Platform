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
            }),

            'following_preview' => collect($this->following_preview ?? [])->map(function ($subscription) {
                $streamer = $subscription->streamer;

                return [
                    'id' => $streamer?->id,
                    'name' => $streamer?->name,
                    'email' => $streamer?->email,
                    'avatar' => $streamer?->profile?->avatar,
                ];
            }),

            'videos_preview' => collect($this->videos_preview ?? [])->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'url' => $video->url,
                    'duration' => $video->duration,
                    'comments_count' => $video->comments_count ?? 0,
                    'created_at' => $video->created_at,
                ];
            }),

            'live_stream' => $this->live_stream ? [
                'id' => $this->live_stream->id,
                'title' => $this->live_stream->title,
                'description' => $this->live_stream->description,
                'status' => $this->live_stream->status,
                'stream_key' => $this->live_stream->stream_key,
                'started_at' => $this->live_stream->started_at,
                'ended_at' => $this->live_stream->ended_at,
                'current_viewers' => $this->live_stream->current_viewers,
                'comments_count' => $this->live_stream->comments_count ?? 0,
                'reactions_count' => $this->live_stream->reactions_count ?? 0,
            ] : null,

            'created_at' => $this->created_at,
        ];
    }
}
