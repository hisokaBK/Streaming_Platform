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
            'background_image' => $this->background_image ,
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

            'created_at' => $this->created_at,
        ];
    }
}
