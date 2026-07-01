<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating community templates
 */
class CreateTemplateRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:5',
            'description' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
            'tags' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:20480', // 20MB max
            'json_file' => 'nullable|file|max:20480',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be valid text.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'title.min' => 'Title must be at least 5 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'tags.max' => 'Tags cannot exceed 500 characters.',
            'attachment.file' => 'The uploaded item must be a valid file.',
            'attachment.max' => 'File size cannot exceed 20MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'title',
            'description' => 'description',
            'content' => 'content',
            'tags' => 'tags',
            'attachment' => 'attachment file',
        ];
    }
}