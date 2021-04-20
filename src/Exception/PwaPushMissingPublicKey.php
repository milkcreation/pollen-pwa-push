<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use InvalidArgumentException;
use Throwable;

class PwaPushMissingPublicKey extends InvalidArgumentException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $message ?: 'PwaPush: VAPID public key required.',
            $code,
            $previous
        );
    }
}