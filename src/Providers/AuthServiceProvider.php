<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Providers;

use Batenburg\JWTGuard\Guards\JWTGuard;
use Batenburg\JWTVerifier\Extractors\Contracts\TokenExtractor as TokenExtractorInterface;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

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

                return new JWTGuard(
                    $authManager->createUserProvider($config['provider']),
                    $jwtVerifier,
                    (string)$tokenExtractor->extract()
                );
            }
        );
    }
}
