<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_banned' => $this->is_banned,
            'banned_at' => $this->banned_at,
            'created_at' => $this->created_at,
            
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'id' => $this->profile?->id,
                    'avatar' => $this->profile?->avatar,
                    'background_image' => $this->profile?->background_image,
                    'bio' => $this->profile?->bio,
                ];
            }),
        ];
    }
}
