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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_initial' => $this->middle_initial,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'contact_number' => $this->contact_number
        ];
    }
}
