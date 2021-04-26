<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

interface PwaPushProxyInterface
{
    /**
     * Récupération de l'instance du gestionnaire de Push Pwa.
     *
     * @return PwaPushInterface
     */
    public function pwaPush(): PwaPushInterface;

    /**
     * Définition du gestionnaire de Push Pwa.
     *
     * @param PwaPushInterface $pwaPush
     *
     * @return void
     */
    public function setPwaPush(PwaPushInterface $pwaPush): void;
}
