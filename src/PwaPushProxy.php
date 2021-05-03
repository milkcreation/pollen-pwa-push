<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Pollen\Support\ProxyResolver;
use RuntimeException;

/**
 * @see PwaPushProxyInterface
 */
trait PwaPushProxy
{
    /**
     * Instance du gestionnaire de Pwa.
     * @var PwaPushInterface|null
     */
    private $pwaPush;

    /**
     * Récupération de l'instance du gestionnaire de Push Pwa.
     *
     * @return PwaPushInterface
     */
    public function pwaPush(): PwaPushInterface
    {
        if ($this->pwaPush === null) {
            try {
                $this->pwaPush = PwaPush::getInstance();
            } catch (RuntimeException $e) {
                $this->pwaPush = ProxyResolver::getInstance(
                    PwaPushInterface::class,
                    PwaPush::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
        }

        return $this->pwaPush;
    }

    /**
     * Définition du gestionnaire de Push Pwa.
     *
     * @param PwaPushInterface $pwaPush
     *
     * @return void
     */
    public function setPwaPush(PwaPushInterface $pwaPush): void
    {
        $this->pwaPush = $pwaPush;
    }
}
