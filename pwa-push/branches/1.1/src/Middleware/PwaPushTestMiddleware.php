<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Middleware;

use Pollen\PwaPush\PwaPushProxy;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;
use Pollen\Routing\BaseMiddleware;
use Pollen\Routing\Exception\NotFoundException;
use Pollen\PwaPush\PwaPushInterface;

class PwaPushTestMiddleware extends BaseMiddleware
{
    use PwaPushProxy;

    /**
     * @param PwaPushInterface|null $pwaPush
     */
    public function __construct(?PwaPushInterface $pwaPush = null)
    {
        if ($pwaPush !== null) {
            $this->setPwaPush($pwaPush);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws NotFoundException
     */
    public function process(PsrRequest $request, RequestHandlerInterface $handler): PsrResponse
    {
        if ($this->pwaPush()->isTestModeEnabled()) {
            return $handler->handle($request);
        }

        throw new NotFoundException(
            'PwaPush Test is disabled.',
            'PwaPush Test disabled'
        );
    }
}