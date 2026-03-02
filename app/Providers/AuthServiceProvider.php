<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\JwtGuard;
use App\Services\JWT\JwtService;

/**
 * This service provider is responsible for extending the default Laravel authentication
 * system to support JWT authentication.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * This method is called after all the other service providers have been registered,
     * including the default Laravel service providers. This is a good place to
     * extend or override the default Laravel services.
     */
    public function boot()
    {
        // Extend the default Laravel authentication system to support JWT authentication
        Auth::extend('jwt', function ($app, $name, array $config) {
            // Create a new instance of the JwtGuard class
            // which will be used to authenticate requests
            return new JwtGuard(
                // Use the user provider specified in the configuration
                Auth::createUserProvider($config['provider']),
                // Use the current request
                $app['request'],
                // Use the JWT service to generate and validate JWT tokens
                $app->make(JwtService::class)
            );
        });
    }

}
