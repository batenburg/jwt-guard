<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Test\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @mixin TestCase
 */
trait ExceptionTesting
{

    public function testADefaultException(): void
    {
        // Execute
        $result = new $this->class();
        // Validate
        $this->assertInstanceOf($this->class, $result);
        $this->assertSame($this->message, $result->getMessage());
        $this->assertSame(0, $result->getCode());
        $this->assertNull($result->getPrevious());
    }

    /**
     * @dataProvider customExceptionScenarioProvider
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function testCustomException(string $message, int $code, ?Throwable $previous): void
    {
        // Execute
        $result = new $this->class($message, $code, $previous);
        // Validate
        $this->assertInstanceOf($this->class, $result);
        $this->assertSame($message, $result->getMessage());
        $this->assertSame($code, $result->getCode());
        $this->assertSame($previous, $result->getPrevious());
    }

    public function customExceptionScenarioProvider(): array
    {
        return [
            'A custom message' => [
                'A custom message',
                0,
                null
            ],
            'A custom code' => [
                'A custom message',
                401,
                null
            ],
            'A custom previous' => [
                'A custom message',
                0,
                $this->createMock(Throwable::class)
            ],
        ];
    }
}
