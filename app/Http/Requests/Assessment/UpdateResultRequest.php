<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating assessment results
 */
class UpdateResultRequest extends FormRequest
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
            'evidence_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:10240',
            'maturity_rating' => 'nullable|integer|min:0|max:5',
            'notes' => 'nullable|string|max:5000',
            'answers' => 'nullable|array',
            'answers.*' => 'nullable|integer|min:0|max:5',
            'is_applicable' => 'nullable|boolean',
            'soa_justification' => 'nullable|string|max:2000',
            'implementation_status' => 'nullable|string|in:not_started,in_progress,completed',
            'treatment_due_date' => 'nullable|date',
            'treatment_pic' => 'nullable|string|max:255',
            'treatment_status' => 'nullable|string|in:open,in_progress,closed',
        ];
    }

    /**
     * Validate answers array structure
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate answers array if provided
            if ($this->has('answers') && is_array($this->answers)) {
                foreach ($this->answers as $key => $value) {
                    if ($value !== null && (!is_numeric($value) || $value < 0 || $value > 5)) {
                        $validator->errors()->add('answers', "Answer at index {$key} must be a number between 0 and 5.");
                        break;
                    }
                }
            }

            // Validate maturity_rating if provided
            if ($this->has('maturity_rating') && $this->maturity_rating !== null) {
                if (!is_numeric($this->maturity_rating) || $this->maturity_rating < 0 || $this->maturity_rating > 5) {
                    $validator->errors()->add('maturity_rating', 'Maturity rating must be a number between 0 and 5.');
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
            'evidence_file.file' => 'The uploaded item must be a valid file.',
            'evidence_file.mimes' => 'Only PDF, JPG, PNG, DOCX, and XLSX files are allowed.',
            'evidence_file.max' => 'File size cannot exceed 10MB.',
            'maturity_rating.integer' => 'Maturity rating must be a number.',
            'maturity_rating.min' => 'Maturity rating must be at least 0.',
            'maturity_rating.max' => 'Maturity rating cannot exceed 5.',
            'treatment_due_date.date' => 'Treatment due date must be a valid date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'evidence_file' => 'evidence file',
            'maturity_rating' => 'maturity rating',
            'treatment_due_date' => 'treatment due date',
            'treatment_pic' => 'treatment person in charge',
        ];
    }
}