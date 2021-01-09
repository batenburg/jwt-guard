<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Integration\Providers;

use Batenburg\JWTGuard\Exceptions\UndefinedUserProviderException;
use Batenburg\JWTGuard\Guards\JWTGuard;
use Batenburg\JWTGuard\Providers\AuthServiceProvider;
use Batenburg\JWTVerifier\Extractors\Contracts\TokenExtractor;
use Batenburg\JWTVerifier\JWTVerifier\JWTVerifier;
use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Providers\AuthServiceProvider
 */
class AuthServiceProviderTest extends TestCase
{
    private Application $application;

    private AuthServiceProvider $authServiceProvider;

    private array $config = [
        'auth' => [
            'guards' => [
                'api' => [
                    'driver' => JWTGuard::class,
                    'provider' => 'users',
                ],
            ],
            'providers' => [
                'users' => [
                    'driver' => 'custom-user-provider',
                    'model' => Authenticatable::class,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();

        $this->application->singleton(
            'config',
            fn() => new ConfigRepository($this->config)
        );
        $this->application->singleton(JWTVerifier::class, fn () => $this->createMock(JWTVerifier::class));
        $this->application->singleton(TokenExtractor::class, fn () => $this->createMock(TokenExtractor::class));

        $this->authServiceProvider = new AuthServiceProvider($this->application);
    }

    /**
     * @covers \Batenburg\JWTGuard\Providers\AuthServiceProvider::boot
     */
    public function testBoot(): void
    {
        $authManager = new AuthManager($this->application);
        $authManager->provider('custom-user-provider', fn () => $this->createMock(UserProvider::class));

        $this->authServiceProvider->boot($authManager);

        $guard = $authManager->guard('api');

        $this->assertInstanceOf(JWTGuard::class, $guard);
    }

    /**
     * @covers \Batenburg\JWTGuard\Providers\AuthServiceProvider::boot
     */
    public function testBootWithNonExistingUserProvider(): void
    {
        $this->expectException(UndefinedUserProviderException::class);

        $authManager = new AuthManager($this->application);

        $this->authServiceProvider->boot($authManager);

        $authManager->guard('api');
    }
}
