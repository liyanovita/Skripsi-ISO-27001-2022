<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for importing assessment sessions from JSON
 */
class ImportSessionRequest extends FormRequest
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
            'json_file' => 'required|file|mimes:json,txt|max:10240', // Max 10MB
            'new_name' => 'nullable|string|max:255|min:3',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'json_file.required' => 'JSON file is required for import.',
            'json_file.file' => 'The uploaded item must be a valid file.',
            'json_file.mimes' => 'Only JSON and TXT files are allowed.',
            'json_file.max' => 'File size cannot exceed 10MB.',
            'new_name.string' => 'New session name must be valid text.',
            'new_name.max' => 'New session name cannot exceed 255 characters.',
            'new_name.min' => 'New session name must be at least 3 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'json_file' => 'JSON file',
            'new_name' => 'new session name',
        ];
    }
}