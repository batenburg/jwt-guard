<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Unit\Exceptions;

use Batenburg\JWTGuard\Exceptions\MethodNotImplementedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Batenburg\JWTGuard\Exceptions\MethodNotImplementedException
 */
class MethodNotImplementedExceptionTest extends TestCase
{

    use ExceptionTesting;

    protected string $class = MethodNotImplementedException::class;

    protected string $message = 'Method not implemented.';
}
