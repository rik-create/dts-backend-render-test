<?php

namespace App\Http\Controllers\v1\modules;


use App\Exceptions\UnauthorizedException;
use App\Exceptions\InvalidException;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenericResponseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\JWT\JwtService;
use App\Services\v1\modules\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\v1\modules\auth\response\LoginResource;
use App\Http\Resources\v1\modules\auth\response\LogoutResource;
use App\Http\Resources\v1\modules\auth\response\RefreshResource;
class AuthController extends Controller
{

    protected $service;

    public function __construct(AuthService $authService) {
        $this->service = $authService;
    }

    /**
     * Login a user
     *
     * This authorize validate user to login into the system
     *
    */
    public function login(Request $request, JwtService $jwt) {

        try{
             $credentials = $request->validate([
                'username' => 'required|string|exists:users,username',
                'password' => 'required|string',
            ]);

            $response = (object) $this->service->loginService($credentials,$jwt);
            return new LoginResource($response);
        }catch(InvalidException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage() ,
                ], 400);
        }catch(\BadFunctionCallException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage() ,
                ], 400);
        }

        catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
        }

    }

    /**
     * Logout a user
     *
     * This enable authorize and authenticated user to logout
     *
    */
    public function logout (Request $request) {

        try{

            $validatedRequest = $request->validate([
                'refresh_token' => 'required|string',
                'logout_all_devices' => 'nullable|boolean',
            ]);

            $response =  (object) $this->service->logoutService($validatedRequest);

            return new LogoutResource($response);
        }
        catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
        }

    }

    /**
     * Refresh user tokens
     *
     * This refresh user token to prolong user access into the system
     *
    */
    public function refreshToken(Request $request, JwtService $jwt)
    {
        try{
            $validatedRequest = $request->validate([
                'refresh_token' => 'required|string',
            ]);

            $response = (object) $this->service->refreshService($validatedRequest, $jwt);

            return new RefreshResource($response);

        }catch(ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage() ,
                ], 404);
        }catch(UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage() ,
                ], 401);
        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
        }
    }

    /**
     * Send password reset link
     * [Forgot] sends an email containing standard Laravel reset token
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $response = $this->service->forgotPasswordService($request->validated());
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password via email token
     * [Reset] updates the user password using the emailed token
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $response = $this->service->resetPasswordService($request->validated());
            return response()->json($response, 200);
        } catch (InvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }
}
