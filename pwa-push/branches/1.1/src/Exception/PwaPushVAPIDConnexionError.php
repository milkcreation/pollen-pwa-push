<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Exception;

use ErrorException;
use Throwable;

class PwaPushVAPIDConnexionError extends ErrorException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $message ?: 'PwaPush: VAPID Connexion on error.',
            $code,
            $previous
        );
    }
}