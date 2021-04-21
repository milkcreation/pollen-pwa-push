<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Exception;

use InvalidArgumentException;
use Throwable;

class PwaPushMissingPrivateKey extends InvalidArgumentException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $message ?: 'PwaPush: VAPID private key required.',
            $code,
            $previous
        );
    }
}