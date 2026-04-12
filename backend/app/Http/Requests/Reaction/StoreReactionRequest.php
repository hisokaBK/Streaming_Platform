<?php

namespace App\Http\Requests\Reaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stream_id' => ['required', 'integer', 'exists:streams,id'],
            'type' => ['required', 'string', Rule::in(['like', 'love', 'haha', 'wow', 'sad', 'angry'])],
        ];
    }
}
