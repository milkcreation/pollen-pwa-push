<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Pollen\Container\BaseServiceProvider;
use Pollen\Pwa\PwaInterface;
use Pollen\PwaPush\Controller\PwaPushController;

class PwaPushServiceProvider extends BaseServiceProvider
{
    /**
     * Liste des services fournis.
     * @var array
     */
    protected $provides = [
        PwaPushInterface::class,
        PwaPushController::class
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
    }
}
