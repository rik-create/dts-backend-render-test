<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use App\Models\UserRefreshToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthHelper
{
    /**
     * Create a new AuthHelper instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
        /**
     * Example method for the service.
     * Replace this with your actual business logic.
     *
     * @param array $data
     * @return mixed
     */

    public static function handleRefreshToken($user, $existingRecord = null)
    {
        $refreshToken = Str::random(60);
        $selector = Str::random(32);
        $expiresAt = null;
        $expiresInMinutes = config('auth.jwt_refresh_ttl');

        if ($existingRecord) {

            $expiresAt = $existingRecord->expires_at;

            $existingRecord->delete();
        } else {
            $expiresAt = Carbon::now()->addMinutes((int) $expiresInMinutes);
        }

        UserRefreshToken::create([
            'user_id' => $user->id,
            'refresh_token_hash' => Hash::make($refreshToken),
            'selector' => $selector,
            'expires_at' => $expiresAt,
        ]);


        return $selector . "." . $refreshToken;
    }
}
