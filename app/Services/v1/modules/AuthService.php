<?php

namespace App\Services\v1\modules;
use App\Http\Resources\v1\modules\auth\MeResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserRefreshToken;
use Illuminate\Support\Str;
use App\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Helpers\AuthHelper;

class AuthService
{
    /**
     * Create a new AuthService instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Constructor logic if needed
    }

    /**
     * Example method for the service.
     * Replace this with your actual business logic.
     *
     * @param array $data
     * @return mixed
     */
    public function loginService(array $data ,$jwt)
    {

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new UnauthorizedException('Invalid credentials');
        }

        $token = $jwt->generateToken($user);

        $refreshToken = AuthHelper::handleRefreshToken($user);

        $response = [
            'success'      => true,
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'refresh_token' => $refreshToken,
            'user'         => new MeResource($user),
        ];

        return $response;
    }

    public function logoutService($request)
    {

        $logoutAllDevices = $request['logout_all_devices'];
        $plainRefreshToken = $request['refresh_token'];

        $user = auth('api')->user();
        $tokens = UserRefreshToken::where('user_id', $user->id)->get();

        if(!$logoutAllDevices) {
            foreach ($tokens as $token) {
                if (Hash::check($plainRefreshToken, $token->refresh_token_hash)) {
                    UserRefreshToken::where('id', $token->id)->delete();
                    break;
                }
            }
        }else {
            UserRefreshToken::where('user_id', $user->id)->delete();
        }

        return [
            'success' => true,
            'message' => 'Successfully logged out.'
        ];
    }

    public function refreshService($request, $jwt)
    {
        $combinedToken = $request['refresh_token'];

        [$selector, $plainRefreshToken] = explode('.', $combinedToken, 2);

        $tokenRecord = UserRefreshToken::where('selector', $selector)->first();

        if (!$tokenRecord) {
            throw new UnauthorizedException('Invalid selector or token.');
        }

        if (Hash::check($plainRefreshToken, $tokenRecord->refresh_token_hash)) {

            if ($tokenRecord->expires_at < Carbon::now()) {
                $tokenRecord->delete();
                throw new UnauthorizedException('Refresh token has expired');
            }

            $user = User::find($tokenRecord->user_id);
            if (!$user) throw new ModelNotFoundException("User not found");

            $newRefreshToken = AuthHelper::handleRefreshToken($user, $tokenRecord);

            $newAccessToken = $jwt->generateToken($user);

            return [
                'success' => true,
                'message' => 'Token refreshed successfully',
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ];
        }

        throw new UnauthorizedException('Invalid refresh token.');
    }


}
