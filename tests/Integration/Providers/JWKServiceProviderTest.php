<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Integration\Providers;

use Batenburg\JWTGuard\Providers\JWKServiceProvider;
use Batenburg\JWTVerifier\JWKFetchers\Adaptors\Contracts\Adaptor as KeyFetcherAdaptor;
use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher as KeyFetchInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Providers\JWKServiceProvider
 */
class JWKServiceProviderTest extends TestCase
{
    private Application $application;

    private JWKServiceProvider $jwkServiceProvider;

    private array $config = [
        'oauth' => [
            'known_client_issuers' => [
                'oauth.lennart.peters' => [
                    'cid' => '47beff72-b006-48f7-9816-4c8a46e22abe',
                    'well_known' => 'https://localhost/auth/well-known',
                ],
            ],
        ],
        'cache' => [
            'default' => 'array',
            'stores' => [
                'array' => [
                    'driver' => 'array',
                    'serialize' => false,
                ],
            ],
            'prefix' => 'laravel_test_cache'
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();

        $this->application->singleton(
            'config',
            fn () => new ConfigRepository($this->config)
        );

        $this->application->singleton(
            'cache',
            function (Application $app) {
                return new CacheManager($app);
            }
        );
        $this->application->singleton(
            'cache.store',
            function (Application $app) {
                return $app['cache']->driver();
            }
        );

        $this->application->singleton(KeyFetcherAdaptor::class, fn () => $this->createMock(KeyFetcherAdaptor::class));

        $this->jwkServiceProvider = new JWKServiceProvider($this->application);
    }

    /**
     * @covers \Batenburg\JWTGuard\Providers\JWKServiceProvider::register
     */
    public function testRegister(): void
    {
        $this->jwkServiceProvider->register();

        $this->assertTrue($this->application->has(KeyFetchInterface::class));
        $this->assertInstanceOf(
            KeyFetchInterface::class,
            $this->application->get(KeyFetchInterface::class)
        );
    }
}
