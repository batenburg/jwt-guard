<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Unit\JWKFetchers;

use Batenburg\JWTGuard\JWKFetchers\KeyCacheFetcher;
use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\JWKFetchers\KeyCacheFetcher
 */
class KeyCacheFetcherTest extends TestCase
{
    private string $issuer = 'oauth';

    /** @var MockObject|KeyFetcher */
    private MockObject $keyFetcher;

    /** @var MockObject|CacheRepository */
    private MockObject $cacheRepository;

    private KeyCacheFetcher $keyCacheFetcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyFetcher = $this->createMock(KeyFetcher::class);
        $this->cacheRepository = $this->createMock(CacheRepository::class);
        $this->keyCacheFetcher = new KeyCacheFetcher(
            $this->issuer,
            $this->keyFetcher,
            $this->cacheRepository
        );
    }

    /**
     * @covers \Batenburg\JWTGuard\JWKFetchers\KeyCacheFetcher::getKeys
     */
    public function testGetKeys(): void
    {
        $this->cacheRepository->expects($this->once())
            ->method('remember')
            ->with(
                "{$this->issuer}.keys",
                3600,
                fn () => $this->keyFetcher->getKeys()
            )
            ->willReturn($keys = [$this->issuer => 'public key']);

        $result = $this->keyCacheFetcher->getKeys();

        $this->assertSame($keys, $result);
    }
}
