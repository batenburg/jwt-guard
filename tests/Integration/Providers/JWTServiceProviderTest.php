<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Integration\Providers;

use Batenburg\JWTVerifier\JWKFetchers\Contracts\KeyFetcher;
use Batenburg\JWTGuard\Providers\JWTServiceProvider;
use Batenburg\JWTVerifier\JWTVerifier\Adaptors\Contracts\Adaptor as JWTVerifierAdaptor;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Providers\JWTServiceProvider
 */
class JWTServiceProviderTest extends TestCase
{
    private Application $application;

    /** @var MockObject|KeyFetcher */
    private MockObject $keyFetcher;

    private JWTServiceProvider $jwtServiceProvider;

    private array $config = [
        'oauth' => [
            'known_client_issuers' => [
                'oauth.lennart.peters' => [
                    'cid' => '47beff72-b006-48f7-9816-4c8a46e22abe',
                    'well_known' => 'https://localhost/auth/well-known',
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
        $this->application->instance('request', new Request());
        $this->application->singleton(
            'config',
            fn () => new ConfigRepository($this->config)
        );

        $this->keyFetcher = $this->createMock(KeyFetcher::class);
        $this->application->singleton(KeyFetcher::class, fn() => $this->keyFetcher);
        $this->application->singleton(JWTVerifierAdaptor::class, fn() => $this->createMock(JWTVerifierAdaptor::class));

        $this->jwtServiceProvider = new JWTServiceProvider($this->application);
    }

    /**
     * @covers \Batenburg\JWTGuard\Providers\JWTServiceProvider::register
     */
    public function testRegister(): void
    {
        $this->jwtServiceProvider->register();

        $this->assertTrue($this->application->has(JWTVerifier::class));
        $this->assertInstanceOf(
            JWTVerifier::class,
            $this->application->get(JWTVerifier::class)
        );
    }
}
