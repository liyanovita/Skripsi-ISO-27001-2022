<?php

namespace App\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating workspace entries
 */
class UpdateWorkspaceEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'treatment_due_date' => $this->input('treatment_due_date') ?: null,
            'treatment_pic' => $this->input('treatment_pic') ?: null,
            'soa_justification' => $this->input('soa_justification') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'is_applicable' => 'nullable|boolean',
            'soa_justification' => 'nullable|string|max:1000',
            'treatment_due_date' => 'nullable|date',
            'treatment_pic' => 'nullable|string|max:255',
            'treatment_status' => 'nullable|string|in:open,in_progress,closed',
            'notes' => 'nullable|string|max:2000',
            'evidence_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx,xls,xlsx,zip|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'soa_justification.max' => 'Justification cannot exceed 1000 characters.',
            'treatment_due_date.date' => 'Treatment due date must be a valid date.',
            'treatment_status.in' => 'Treatment status must be one of: open, in_progress, closed.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'is_applicable' => 'applicability',
            'soa_justification' => 'justification',
            'treatment_due_date' => 'treatment due date',
            'treatment_pic' => 'person in charge',
            'treatment_status' => 'treatment status',
        ];
    }
}