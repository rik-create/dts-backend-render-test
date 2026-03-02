<?php
namespace App\Http\Resources\v1\modules\auth\response;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
  public static $wrap = null;
    /*Scramble reads these types automatically.*/

  public function toArray(Request $request): array{
      return [
        'success'       => $this->success,
        'message'       => $this->message,
        'access_token'  => $this->access_token,
        'token_type'    => $this->token_type,
        'refresh_token' => $this->refresh_token,
        'user'          => $this->user,
    ];
  }

}
