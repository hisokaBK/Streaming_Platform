<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'background_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
