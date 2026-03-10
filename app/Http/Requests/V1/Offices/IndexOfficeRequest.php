<?php

namespace App\Http\Requests\V1\Offices;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRoleEnum;

class IndexOfficeRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search'   => ['nullable', 'string', 'max:255'],
            'sort_by'  => ['nullable', 'string', 'in:id,name,code,is_active,created_at,updated_at'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
