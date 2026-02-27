<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JWT\JwtService;
use Symfony\Component\HttpFoundation\Response;

class GetClaimsMiddleware
{

    protected JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }



    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  \App\Services\JWT\JwtService  $jwt
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

            // Get the claims from the token
            $claims = $token->claims()->all();

            // Attach the claims to the request
            $request->merge(['claims' => $claims]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
