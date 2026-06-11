<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for sending notifications via webhook
 */
class SendNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow webhook calls from n8n or other services
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'channels' => 'required|array|min:1',
            'channels.*' => 'required|string|in:telegram',
            'template' => 'required|string|in:capa_overdue,capa_upcoming',
            'data' => 'required|array',
            'data.pic' => 'required|string|max:255',
            'data.control_code' => 'required|string|max:50',
            'data.control_title' => 'required|string|max:500',
            'data.due_date' => 'required|date_format:Y-m-d',
            'data.days_left' => 'required|integer',
            'data.session_name' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'channels.required' => 'At least one notification channel is required.',
            'channels.array' => 'Channels must be an array.',
            'channels.*.in' => 'Invalid notification channel. Only "telegram" is supported.',
            'template.required' => 'Notification template is required.',
            'template.in' => 'Invalid template. Must be "capa_overdue" or "capa_upcoming".',
            'data.required' => 'Notification data is required.',
            'data.pic.required' => 'Person in charge (pic) is required.',
            'data.control_code.required' => 'Control code is required.',
            'data.control_title.required' => 'Control title is required.',
            'data.due_date.required' => 'Due date is required.',
            'data.due_date.date_format' => 'Due date must be in Y-m-d format.',
            'data.days_left.required' => 'Days left is required.',
            'data.session_name.required' => 'Session name is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'channels' => 'notification channels',
            'template' => 'notification template',
            'data' => 'notification data',
            'data.pic' => 'person in charge',
            'data.control_code' => 'control code',
            'data.control_title' => 'control title',
            'data.due_date' => 'due date',
            'data.days_left' => 'days left',
            'data.session_name' => 'session name',
        ];
    }
}