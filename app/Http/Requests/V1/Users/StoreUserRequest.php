<?php

namespace App\Http\Requests\V1\Users;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRoleEnum;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Refactor authorization to use Granular Group/Module Permissions matrix.
        $userId = $this->attributes->get('user_id');
        $user = \App\Models\User::find($userId);

        return $user && $user->user_role_id === UserRoleEnum::ADMIN->value;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Identifiers
            'username' => ['nullable', 'string', 'max:150', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            // Security
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Requires password_confirmation in payload

            // Profile Info
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:100'],

            // --- RELATIONS ---

            // Ensures office exists in the database
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],

            // Ensures groups is an array, and each item exists in the user_groups table
            'groups' => ['nullable', 'array'],
            'groups.*' => ['integer', 'exists:user_groups,id'],
        ];
    }
}
