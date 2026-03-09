<?php

namespace App\Http\Requests\V1\Offices;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRoleEnum;

class BulkDeleteOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Refactor authorization to use Granular Group/Module Permissions matrix.
        $userId = $this->attributes->get('user_id');
        $user = \App\Models\User::find($userId);

        return $user && $user->user_role_id === UserRoleEnum::ADMIN->value;
    }

    public function rules(): array
    {
        return [
            'office_ids'   => ['required', 'array', 'min:1'],
            'office_ids.*' => ['required', 'integer', 'exists:offices,id'],
        ];
    }
}
