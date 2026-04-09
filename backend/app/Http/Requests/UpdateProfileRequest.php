<?php

namespace App\Http\Requests;

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
            'avatar' => 'nullable|image|max:2048',
            'background_image' => 'nullable|image|max:4096',
            'bio' => 'nullable|string|max:1000',
        ];
    }
}
