<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use App\Services\JWT\JwtService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JwtMiddleware
 *
 * This middleware is responsible for validating the JWT token sent in the Authorization header of each request.
 * If the token is invalid or missing, a 401 Unauthorized response is returned.
 */
class JwtMiddleware
{
    protected $jwt;

    /**
     * Inject the JwtService via the constructor.
     *
     * @param \App\Services\JWT\JwtService $jwt
     */
    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the Authorization header from the request
        $header = $request->header('Authorization');

        // If the header is missing or does not start with 'Bearer ', return an unauthorized response
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        // Extract the token from the header
        $tokenString = substr($header, 7);

        // Try to parse the token
        try {
            $token = $this->jwt->parseToken($tokenString);

            // If the token is invalid, return an unauthorized response
            if (!$this->jwt->validateToken($token)) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            // Get user id from sub claim
            $userId = $token->claims()->get('sub');

            // Attach the user ID to the request
            $request->attributes->set('user_id', $userId);

            // Fetch the user and set as the authenticated user for the 'api' guard
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 401);
            }
            auth('api')->setUser($user);

            // Attach claims dito na rin para di na kailangan ng GetClaimsMiddleware
            $request->attributes->set('jwt_claims', $token->claims()->all());

        // If there is an exception while parsing the token, return an unauthorized response
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Call the next middleware in the chain
        return $next($request);
    }
}
