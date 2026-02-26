<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar_cropped' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|boolean',
            'timezone' => 'nullable|string|timezone',
            'language' => 'nullable|string|in:en',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'avatar.image' => 'The avatar must be an image.',
            'avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif.',
            'avatar.max' => 'The avatar may not be greater than 2MB.',
            'date_of_birth.before' => 'The date of birth must be before today.',
            'role.required' => 'Please select a user role.',
            'role.in' => 'The selected role is invalid.',
            'is_active.required' => 'Please select account status.',
            'is_active.boolean' => 'The account status must be active or inactive.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'language.in' => 'Please select a valid language.',
        ];
    }
}
