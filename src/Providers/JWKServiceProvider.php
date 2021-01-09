<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Providers;

use Batenburg\JWTGuard\JWKFetchers\KeyCacheFetcher;
use Batenburg\JWTVerifier\JWKFetchers\Adaptors\Contracts\Adaptor as KeyFetcherAdaptor;
use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher as KeyFetchInterface;
use Batenburg\JWTVerifier\JWKFetchers\KeyCombiner;
use Batenburg\JWTVerifier\JWKFetchers\KeyFetcher;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class JWKServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            KeyFetchInterface::class,
            function (Application $application) {
                /** @var ConfigRepository $configRepository */
                $configRepository = $application->make(ConfigRepository::class);
                /** @var CacheRepository $cacheRepository */
                $cacheRepository = $application->make(CacheRepository::class);
                /** @var KeyFetcherAdaptor $keyFetcherAdaptor */
                $keyFetcherAdaptor = $application->make(KeyFetcherAdaptor::class);

                $keyFetchers = [];

                foreach ($configRepository->get('oauth.known_client_issuers') as $issuer => $client) {
                    $keyFetcher = new KeyFetcher(
                        new GuzzleClient(),
                        $client['well_known'],
                        $keyFetcherAdaptor
                    );

                    $keyFetchers[] = new KeyCacheFetcher($issuer, $keyFetcher, $cacheRepository);
                }

                return new KeyCombiner(...$keyFetchers);
            }
        );
    }
}
