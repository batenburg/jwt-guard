<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Providers;

use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher as KeyFetchInterface;
use Batenburg\JWTVerifier\JWTVerifier\Adaptors\Contracts\Adaptor as JWTVerifierAdaptor;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class JWTServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            JWTVerifier::class,
            function (Application $application) {
                /** @var KeyFetchInterface $keyFetcher */
                $keyFetcher = $application->make(KeyFetchInterface::class);
                /** @var ConfigRepository $config */
                $config = $application->make(ConfigRepository::class);
                /** @var JWTVerifierAdaptor $jwtVerifierAdaptor */
                $jwtVerifierAdaptor = $application->make(JWTVerifierAdaptor::class);

                return new JWTVerifier(
                    $keyFetcher->getKeys(),
                    $config->get('oauth.known_client_issuers'),
                    $jwtVerifierAdaptor
                );
            }
        );
    }
}
