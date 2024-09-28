<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic.
    }

    public function rules(): array
    {
        return [
            'search' => 'required|string|max:255', // Ensure a valid search term is provided.
        ];
    }

    public function messages(): array
    {
        return [
            'search.required' => 'Please enter a search term.',
            'search.string' => 'The search term must be a valid string.',
            'search.max' => 'The search term cannot exceed 255 characters.',
        ];
    }

    // Handle validation errors
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}