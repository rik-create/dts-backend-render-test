<?php

namespace App\Http\Requests\V1\Offices;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRoleEnum;

class StoreOfficeRequest extends FormRequest
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
            'name'      => ['required', 'string', 'max:255', 'unique:offices,name'],
            'code'      => ['nullable', 'string', 'max:50', 'unique:offices,code'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
