<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Integration\Providers;

use Batenburg\JWTGuard\Providers\TokenServiceProvider;
use Batenburg\JWTVerifier\Extractors\Contracts\TokenExtractor;
use Batenburg\JWTVerifier\Extractors\TokenExtractorCombiner;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Providers\TokenServiceProvider
 */
class TokenServiceProviderTest extends TestCase
{
    private Application $application;

    private TokenServiceProvider $tokenServiceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();

        $this->application->instance('request', new Request());

        $this->tokenServiceProvider = new TokenServiceProvider($this->application);
    }

    /**
     * @covers \Batenburg\JWTGuard\Providers\TokenServiceProvider::register
     */
    public function testRegister(): void
    {
        $this->tokenServiceProvider->register();

        $this->assertTrue($this->application->has(TokenExtractorCombiner::class));
        $this->assertInstanceOf(
            TokenExtractorCombiner::class,
            $this->application->get(TokenExtractorCombiner::class)
        );

        $this->assertTrue($this->application->has(TokenExtractor::class));
        $this->assertInstanceOf(
            TokenExtractorCombiner::class,
            $this->application->get(TokenExtractor::class)
        );
    }
}
