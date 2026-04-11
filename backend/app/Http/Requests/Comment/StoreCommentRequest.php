<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stream_id' => ['required', 'integer', 'exists:streams,id'],
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}
