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
            'description' => 'required|string|min:10|max:2000',
            'tags' => 'nullable|string|max:500',
            'json_file' => 'required|file|mimes:json,txt|max:10240',
        ];
    }

    /**
     * Validate the JSON file content after validation passes
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('json_file')) {
                try {
                    $content = file_get_contents($this->file('json_file')->getRealPath());
                    $data = json_decode($content, true);

                    // Validate JSON structure
                    if (!$data || !is_array($data)) {
                        $validator->errors()->add('json_file', 'Invalid JSON format: file must contain valid JSON data.');
                        return;
                    }

                    // Validate required structure
                    if (!isset($data['session']) && !isset($data['results'])) {
                        $validator->errors()->add('json_file', 'Invalid JSON structure: missing session or results data.');
                        return;
                    }

                    // Validate results array
                    $results = $data['session']['results'] ?? $data['results'] ?? [];
                    if (!is_array($results) || empty($results)) {
                        $validator->errors()->add('json_file', 'Invalid JSON structure: results array is empty or missing.');
                        return;
                    }

                } catch (\Exception $e) {
                    $validator->errors()->add('json_file', 'Failed to parse JSON file: ' . $e->getMessage());
                }
            }
        });

        return $validator;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Template title is required.',
            'title.string' => 'Template title must be valid text.',
            'title.max' => 'Template title cannot exceed 255 characters.',
            'title.min' => 'Template title must be at least 5 characters.',
            'description.required' => 'Template description is required.',
            'description.string' => 'Template description must be valid text.',
            'description.min' => 'Template description must be at least 10 characters.',
            'description.max' => 'Template description cannot exceed 2000 characters.',
            'tags.max' => 'Tags cannot exceed 500 characters.',
            'json_file.required' => 'JSON file is required.',
            'json_file.file' => 'The uploaded item must be a valid file.',
            'json_file.mimes' => 'Only JSON and TXT files are allowed.',
            'json_file.max' => 'File size cannot exceed 10MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'template title',
            'description' => 'template description',
            'tags' => 'template tags',
            'json_file' => 'JSON file',
        ];
    }
}