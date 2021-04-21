<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Middleware;

use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;
use Pollen\Routing\BaseMiddleware;
use Pollen\PwaPush\PwaPushInterface;

class PwaPushTestMiddleware extends BaseMiddleware
{
    /**
     * @var PwaPushInterface
     */
    protected $pwaPush;

    /**
     * @param PwaPushInterface $pwaPush
     */
    public function __construct(PwaPushInterface $pwaPush)
    {
        $this->pwaPush = $pwaPush;
    }

    /**
     * @inheritDoc
     *
     * @throws NotFoundException
     */
    public function process(PsrRequest $request, RequestHandlerInterface $handler): PsrResponse
    {
        if ($this->pwaPush->isTestModeEnabled()) {
            return $handler->handle($request);
        }
        throw new NotFoundException();
    }
}