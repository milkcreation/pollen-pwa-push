<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Pollen\Container\BootableServiceProvider;
use Pollen\Pwa\PwaInterface;
use Pollen\PwaPush\Controller\PwaPushController;
use Pollen\PwaPush\Middleware\PwaPushTestMiddleware;

class PwaPushServiceProvider extends BootableServiceProvider
{
    /**
     * Liste des services fournis.
     * @var array
     */
    protected $provides = [
        PwaPushInterface::class,
        PwaPushController::class,
        'routing.middleware.pwa-push.test',
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(
            PwaPushInterface::class,
            function () {
                return new PwaPush(
                    $this->getContainer()->get(PwaInterface::class),
                    [],
                    $this->getContainer()
                );
            }
        );

        $this->getContainer()->share(
            PwaPushController::class,
            function () {
                return new PwaPushController(
                    $this->getContainer()->get(PwaPushInterface::class),
                    $this->getContainer()
                );
            }
        );

        $this->getContainer()->add('routing.middleware.pwa-push.test', function () {
           return new PwaPushTestMiddleware($this->getContainer()->get(PwaPushInterface::class));
        });
    }
}
