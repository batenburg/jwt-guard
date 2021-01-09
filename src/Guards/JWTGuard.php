<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Guards;

use Batenburg\JWTGuard\Exceptions\MethodNotImplementedException;
use Batenburg\JWTVerifier\JWT\JWT;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;

class JWTGuard implements Guard
{
    private UserProvider $userProvider;

    private JWTVerifier $jwtVerifier;

    private string $token;

    private ?Authenticatable $user = null;

    public function __construct(UserProvider $userProvider, JWTVerifier $JWTVerifier, string $token)
    {
        $this->userProvider = $userProvider;
        $this->jwtVerifier = $JWTVerifier;
        $this->token = $token;
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    public function check(): bool
    {
        return $this->id() !== null;
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    public function guest(): bool
    {
        return $this->id() === null;
    }

    /**
     * @return Authenticatable|null
     * @throws AuthenticationException
     */
    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $this->user = $this->userProvider->retrieveById($this->id());

        return $this->user;
    }

    /**
     * @return int|null
     * @throws AuthenticationException
     */
    public function id(): ?int
    {
        $jwt = $this->verifyTokenAndHydrateJwt();

        if (! $jwt->getClaims()->has('sub')) {
            return null;
        }

        return (int)$jwt->getClaims()->get('sub');
    }

    /**
     * @param array $credentials
     * @return bool
     * @throws MethodNotImplementedException
     */
    public function validate(array $credentials = []): bool
    {
        throw new MethodNotImplementedException;
    }

    /**
     * @param Authenticatable $user
     * @throws MethodNotImplementedException
     */
    public function setUser(Authenticatable $user): void
    {
        throw new MethodNotImplementedException;
    }

    /**
     * @return JWT
     * @throws AuthenticationException
     */
    private function verifyTokenAndHydrateJwt(): JWT
    {
        try {
            $jwt = $this->jwtVerifier->verify($this->token);
        } catch (Exception $exception) {
            throw new AuthenticationException("JWT: {$exception->getMessage()}.", [$this]);
        }

        return $jwt;
    }
}
