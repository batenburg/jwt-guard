<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Providers;

use Batenburg\JWTVerifier\Extractors\Contracts\TokenExtractor as TokenExtractorInterface;
use Batenburg\JWTVerifier\Extractors\TokenByBearerExtractor;
use Batenburg\JWTVerifier\Extractors\TokenByRequestExtractor;
use Batenburg\JWTVerifier\Extractors\TokenExtractorCombiner;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(
            TokenExtractorCombiner::class,
            function (Application $application) {
                $request = $application->make(Request::class);

                return new TokenExtractorCombiner(
                    new TokenByBearerExtractor($request),
                    new TokenByRequestExtractor($request)
                );
            }
        );

        $this->app->bind(
            TokenExtractorInterface::class,
            fn (Application $application): TokenExtractorCombiner => $application->make(TokenExtractorCombiner::class)
        );
    }
}
