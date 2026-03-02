<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Services\JWT\JwtService;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class JwtGuard
 *
 * This class is responsible for validating JWT tokens in the Authorization header
 * and retrieving the corresponding user from the database.
 */
class JwtGuard implements Guard
{
    /**
     * The currently authenticated user
     * @var Authenticatable|null
     */
    protected $user;

    /**
     * The user provider implementation
     * @var UserProvider
     */
    protected $provider;

    /**
     * The current request instance
     * @var Request
     */
    protected $request;

    /**
     * The JWT service implementation
     * @var JwtService
     */
    protected $jwt;

    /**
     * Constructor
     *
     * @param UserProvider $provider
     * @param Request $request
     * @param JwtService $jwt
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        JwtService $jwt
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->jwt = $jwt;
    }

    /**
     * Get the currently authenticated user
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        // If the user is already set, return it
        if ($this->user) {
            return $this->user;
        }

        // Get the JWT token from the Authorization header
        $tokenString = $this->getTokenFromRequest();

        // If the token string is empty, return null
        if (!$tokenString) {
            return null;
        }

        try {
            // Parse the JWT token
            $token = $this->jwt->parseToken($tokenString);

            // If the token is invalid, return null
            if (!$this->jwt->validateToken($token)) {
                return null;
            }

            // Get the user ID from the JWT token claims
            $userId = $token->claims()->get('sub');

            // Retrieve the user from the database
            $this->user = $this->provider->retrieveById($userId);

        } catch (\Exception $e) {
            // If there is an exception while parsing the token, return null
            return null;
        }

        return $this->user;
    }

    /**
     * Check if the user is authenticated
     *
     * @return bool
     */
    public function check()
    {
        // Return true if the user is not null
        return !is_null($this->user());
    }

    /**
     * Check if the user is a guest
     *
     * @return bool
     */
    public function guest()
    {
        // Return true if the user is null
        return is_null($this->user());
    }

    /**
     * Get the ID of the currently authenticated user
     *
     * @return mixed
     */
    public function id()
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Validate the given authentication credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        // Return false as this guard does not support validation
        return false;
    }

    /**
     * Set the user
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the JWT token from the Authorization header
     *
     * @return string|null
     */
    protected function getTokenFromRequest()
    {
        // Get the Authorization header from the request
        $header = $this->request->header('Authorization');

        // If the header is missing or does not start with 'Bearer ', return null
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        // Return the JWT token string
        return substr($header, 7);
    }

    /**
     * Check if the user is set
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        // Return true if the user property is set
        return isset($this->user);

    }
}
