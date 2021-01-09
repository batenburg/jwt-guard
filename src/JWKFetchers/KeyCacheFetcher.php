<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\JWKFetchers;

use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class KeyCacheFetcher implements KeyFetcher
{
    private const EXPIRES_AFTER_IN_SECONDS = 3600;

    private string $issuer;

    private KeyFetcher $keyFetcher;

    private CacheRepository $cacheRepository;

    public function __construct(string $issuer, KeyFetcher $keyFetcher, CacheRepository $cacheRepository)
    {
        $this->issuer = $issuer;
        $this->keyFetcher = $keyFetcher;
        $this->cacheRepository = $cacheRepository;
    }

    public function getKeys(): array
    {
        return $this->cacheRepository->remember(
            "{$this->issuer}.keys",
            self::EXPIRES_AFTER_IN_SECONDS,
            fn () => $this->keyFetcher->getKeys()
        );
    }
}
