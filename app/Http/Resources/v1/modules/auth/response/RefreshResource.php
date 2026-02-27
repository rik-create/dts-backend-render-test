<?php

namespace App\Http\Resources\v1\modules\auth\response;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefreshResource extends JsonResource
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
            'success' => $this->success,
            'message' => $this->message,
            'refresh_token' => $this->refresh_token ?? null,
            'access_token' => $this->access_token ?? null,
        ];
    }
}
