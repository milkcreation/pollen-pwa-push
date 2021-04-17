<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Pollen\Pwa\PwaProxyInterface;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Concerns\ResourcesAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Pollen\Support\Proxy\EventProxyInterface;
use Pollen\Support\Proxy\HttpRequestProxyInterface;
use Pollen\Support\Proxy\PartialProxyInterface;
use Pollen\Support\Proxy\RouterProxyInterface;

interface PwaPushInterface extends
    BootableTraitInterface,
    ConfigBagAwareTraitInterface,
    ResourcesAwareTraitInterface,
    ContainerProxyInterface,
    HttpRequestProxyInterface,
    EventProxyInterface,
    PartialProxyInterface,
    PwaProxyInterface,
    RouterProxyInterface
{
    /**
     * Initialisation du gestionnaire de push PWA.
     *
     * @return static
     */
    public function boot(): PwaPushInterface;
}
