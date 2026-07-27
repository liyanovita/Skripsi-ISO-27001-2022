<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating user profile
 */
class UpdateProfileRequest extends FormRequest
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
        $user = $this->user();

        return [
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'job_title' => 'nullable|string|max:255',
            'role_description' => 'nullable|string|max:1000',
            'organization_name' => 'nullable|string|max:255|min:3',
            'business_sector' => 'nullable|string|max:255|min:3',
            'organization_scale' => 'nullable|string|max:255|min:3',
            'it_governance_structure' => 'nullable|string|max:2000',
            'isms_scope' => 'nullable|string|max:2000',
            'organization_description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use by another user.',
        ];
    }
}
