<?php

namespace App\Services\v1\modules;
use App\Exceptions\InvalidException;
use App\Exceptions\UnauthorizedException;
use App\Helpers\AuthHelper;
use App\Http\Resources\v1\modules\auth\MeResource;
use App\Models\User;
use App\Models\UserRefreshToken;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

        $user = User::where('username', $data['username'])->first();
        $isValid = false;

        if ($user) {
            if (str_starts_with($user->password, 'pbkdf2_sha256$')) {
                // Decode legacy Django PBKDF2 password
                $parts = explode('$', $user->password);
                if (count($parts) === 4) {
                    $iterations = (int) $parts[1];
                    $salt = $parts[2];
                    $hash = base64_decode($parts[3]);

                    $calc = hash_pbkdf2('sha256', $data['password'], $salt, $iterations, 32, true);
                    if (hash_equals($hash, $calc)) {
                        $isValid = true;

                        // Automatically upgrade password to Laravel Bcrypt for future logins
                        $user->password = Hash::make($data['password']);
                        $user->save();
                    }
                }
            } else {
                try {
                    $isValid = Hash::check($data['password'], $user->password);
                } catch (\Exception $e) {
                    $isValid = false;
                }
            }
        }

        if (!$isValid) {
            throw new UnauthorizedException('Invalid credentials');
        }

        if (!$user->is_active) {
            throw new UnauthorizedException('Your account is inactive. Please contact your administrator.');
        }

        $office = $user->office()->withTrashed()->first();
        if ($office && ($office->trashed() || !$office->is_active)) {
            throw new UnauthorizedException('Your assigned office is currently inactive or deleted. Please contact your administrator.');
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

    public function forgotPasswordService(array $data)
    {
        // TODO: In production, ensure MAIL_MAILER in .env is set to a real SMTP server (e.g., smtp, ses, mailgun) instead of 'log' to actually deliver the email.
        $status = Password::broker()->sendResetLink(
            ['email' => $data['email']]
        );

        if ($status !== Password::RESET_LINK_SENT && $status !== Password::INVALID_USER) {
            throw new \Exception(__($status));
        }

        return [
            'success' => true,
            'message' => 'If the email exists in our records, we have emailed your password reset link.',
        ];
    }

    public function resetPasswordService(array $data)
    {
        $status = Password::broker()->reset(
            $data,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new InvalidException(__($status));
        }

        return [
            'success' => true,
            'message' => 'Your password has been reset successfully.',
        ];
    }
}
