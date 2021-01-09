<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Providers;

use Batenburg\JWTGuard\Exceptions\UndefinedUserProviderException;
use Batenburg\JWTGuard\Guards\JWTGuard;
use Batenburg\JWTVerifier\Extractors\Contracts\TokenExtractor as TokenExtractorInterface;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(AuthManager $authManager): void
    {
        $authManager->extend(
            JWTGuard::class,
            function (Application $application, string $name, array $config) use ($authManager) {
                /** @var JWTVerifier $jwtVerifier */
                $jwtVerifier = $application->make(JWTVerifier::class);
                /** @var TokenExtractorInterface $tokenExtractor */
                $tokenExtractor = $application->make(TokenExtractorInterface::class);

                try {
                    $userProvider = $authManager->createUserProvider($config['provider']);

                    if ($userProvider === null) {
                        throw new UndefinedUserProviderException;
                    }
                } catch (InvalidArgumentException $exception) {
                    throw new UndefinedUserProviderException;
                }

                return new JWTGuard(
                    $userProvider,
                    $jwtVerifier,
                    (string)$tokenExtractor->extract()
                );
            }
        );
    }
}
