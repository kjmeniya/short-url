<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        // System roles have restricted validation
        if ($role && $role->isSystemRole()) {
            return [
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z_]+$/',
                Rule::unique('roles', 'name')->ignore($this->role)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
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
            'name.regex' => 'The role name must contain only lowercase letters and underscores.',
            'name.unique' => 'A role with this name already exists.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'role name',
            'display_name' => 'display name',
            'is_active' => 'status',
        ];
    }
}
