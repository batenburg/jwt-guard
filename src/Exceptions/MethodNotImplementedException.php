<?php

declare(strict_types=1);

namespace Batenburg\JWTGuard\Exceptions;

use Exception;
use Throwable;

class MethodNotImplementedException extends Exception
{
    public function __construct(string $message = 'Method not implemented.', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
