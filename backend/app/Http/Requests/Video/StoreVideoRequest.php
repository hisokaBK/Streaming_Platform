<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stream_id' => ['required', 'integer', 'exists:streams,id'],
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:102400'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
