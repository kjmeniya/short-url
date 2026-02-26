<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users to submit contact form
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name',
            'name.min' => 'Name must be at least 2 characters long',
            'name.max' => 'Name cannot exceed 100 characters',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'subject.required' => 'Please enter a subject',
            'subject.min' => 'Subject must be at least 3 characters long',
            'subject.max' => 'Subject cannot exceed 200 characters',
            'message.required' => 'Please enter your message',
            'message.min' => 'Message must be at least 10 characters long',
            'message.max' => 'Message cannot exceed 2000 characters',
        ];
    }
}
