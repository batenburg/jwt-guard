<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Unit\Exceptions;

use Batenburg\JWTGuard\Exceptions\UndefinedUserProviderException;
use PHPUnit\Framework\TestCase;

class UndefinedUserProviderExceptionTest extends TestCase
{

    use ExceptionTesting;

    protected string $class = UndefinedUserProviderException::class;

    protected string $message = 'Undefined user provider.';
}
