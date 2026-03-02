<?php

namespace App\Http\Resources\v1\modules\auth\response;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogoutResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
