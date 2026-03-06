<?php

namespace App\Services\JWT;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\Clock\SystemClock;
use App\Models\User;
use DateTimeImmutable;

/**
 * Class JwtService
 *
 * @package App\Services\JWT
 *
 * Responsible for generating and validating JWT tokens.
 */
class JwtService
{
    /**
     * @var Configuration
     */
    private Configuration $config;

    /**
     * JwtService constructor.
     */
    public function __construct()
    {
        /**
         * Create a new configuration for generating JWT tokens.
         * The configuration is set up to use the HS256 algorithm with a secret key.
         */

        $privateKey = InMemory::plainText(
            file_get_contents(base_path(env('JWT_PRIVATE_KEY_PATH')))
        );

        $publicKey = InMemory::plainText(
            file_get_contents(base_path(env('JWT_PUBLIC_KEY_PATH')))
        );

        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $privateKey,
            $publicKey
        );



        $this->config->setValidationConstraints(
            new SignedWith($this->config->signer(), $publicKey),
            new LooseValidAt(SystemClock::fromSystemTimezone())
        );
    }

    /**
     * Generate a JWT token for the given user.
     *
     * @param User $user
     * @return string
     */
    public function generateToken(User $user)
    {
        /**
         * Get the current timestamp.
         */
        $now = new DateTimeImmutable();

        /**
         * Build a new JWT token.
         * The token is set to expire in 1 hour.
         */
        $token = $this->config->builder()
            /**
             * Set the issuer of the token.
             * This is the URL of the application.
             */
            ->issuedBy(config('app.url'))                                 // iss
            /**
             * Set the audience of the token.
             * This is the URL of the application.
             */
            ->permittedFor(config('app.url'))                       // aud
            /**
             * Set the id of the token.
             * This is a random string.
             */
            ->identifiedBy(bin2hex(random_bytes(16)))           // jti
            /**
             * Set the timestamp of when the token was issued.
             */
            ->issuedAt($now)
            /**
             * Set the timestamp of when the token expires.
             */
            ->expiresAt($now->modify('+15 minutes'))
            /**
             * Set the user id of the token.
             * This is the id of the user.
             */
            ->relatedTo($user->id)                                        // sub
            /**
             * Set the email of the token.
             * This is the email of the user.
             */
            ->withClaim('email', $user->email)

            //  ETO NA YUNG IDADAGDAG NATIN PARA SA SUPER ADMIN TO
            ->withClaim('is_superuser', (bool) $user->is_superuser)
           
            /**
             * Get the token.
             */
            ->getToken($this->config->signer(), $this->config->signingKey());

        /**
         * Return the token as a string.
         */
        return $token->toString();
    }

    /**
     * Parse the given JWT token.
     *
     * @param string $token
     * @return mixed
     */
    public function parseToken( $token)
    {
        /**
         * Parse the token.
         */
        return $this->config->parser()->parse($token);
    }

    /**
     * Validate the given JWT token.
     *
     * @param \Lcobucci\JWT\Token $token
     * @return bool
     */
    public function validateToken($token)
    {
        /**
         * Get the validation constraints for the token.
         */
        $constraints = $this->config->validationConstraints();

        /**
         * Validate the token.
         */
        return $this->config->validator()->validate($token, ...$constraints);
    }
}
