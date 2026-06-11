<?php

namespace App\Http\Requests\Governance;

use App\Services\Governance\KnowledgeBaseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request for updating knowledge base resources
 */
class UpdateKnowledgeBaseRequest extends FormRequest
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
            'category' => ['required', 'string', Rule::in(KnowledgeBaseService::CATEGORIES)],
            'description' => 'nullable|string|max:1000',
            'content' => 'required|string|min:10',
            'format' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,md,csv|max:10240',
            'icon' => 'nullable|string|max:100',
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
            'category.required' => 'Category is required.',
            'category.in' => 'Category must be one of: guides, templates, sop, evidence.',
            'content.required' => 'Content is required.',
            'content.min' => 'Content must be at least 10 characters.',
            'attachment.mimes' => 'Attachment must be a PDF, Word, Excel, text, Markdown, or CSV file.',
            'attachment.max' => 'Attachment cannot exceed 10 MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'resource title',
            'category' => 'resource category',
            'description' => 'resource description',
            'content' => 'resource content',
            'format' => 'file format',
            'size' => 'file size',
            'attachment' => 'attachment file',
            'icon' => 'icon',
        ];
    }
}
