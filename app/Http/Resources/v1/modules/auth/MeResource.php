<?php

namespace App\Http\Resources\v1\modules\auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->id,
            'user_role_id' => $this->user_role_id,
            'office_id' => $this->office_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'username' => $this->username,
            'contact_number' => $this->contact_number,
            'is_active' => $this->is_active,
        ];
    }
}
