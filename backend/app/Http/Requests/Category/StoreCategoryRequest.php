<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
     public function authorize(): bool
     {
         return true;
     }

     public function rules(): array
     {
         return [
             'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
             'description' => ['nullable', 'string'],
         ];
     }
}
