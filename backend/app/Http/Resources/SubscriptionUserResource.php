<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->streamer ?? $this->subscriber;

        return [
            'subscription_id' => $this->id,
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'avatar' => $user?->profile?->avatar,
            'followed_at' => $this->created_at,
        ];
    }
}
