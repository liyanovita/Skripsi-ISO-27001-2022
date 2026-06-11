<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for rating community templates
 */
class RateTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'stars' => 'required|integer|min:1|max:5',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'stars.required' => 'Rating is required.',
            'stars.integer' => 'Rating must be a number.',
            'stars.min' => 'Rating must be at least 1 star.',
            'stars.max' => 'Rating cannot exceed 5 stars.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'stars' => 'rating',
        ];
    }
}