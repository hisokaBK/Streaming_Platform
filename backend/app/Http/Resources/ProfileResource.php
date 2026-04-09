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
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'background_image' => $this->background_image ? asset('storage/' . $this->background_image) : null,
            'bio' => $this->bio,
            'created_at' => $this->created_at,
        ];
    }
}
