<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Pollen\Pwa\PwaProxyInterface;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Concerns\ResourcesAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Pollen\Support\Proxy\DbProxyInterface;
use Pollen\Support\Proxy\EventProxyInterface;
use Pollen\Support\Proxy\HttpRequestProxyInterface;
use Pollen\Support\Proxy\PartialProxyInterface;
use Pollen\Support\Proxy\RouterProxyInterface;

interface PwaPushInterface extends
    BootableTraitInterface,
    ConfigBagAwareTraitInterface,
    ResourcesAwareTraitInterface,
    ContainerProxyInterface,
    DbProxyInterface,
    EventProxyInterface,
    HttpRequestProxyInterface,
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

    /**
     * Création de la table de base de données.
     *
     * @return void
     */
    public function dbMigrate(): void;

    /**
     * Récupération de la clé publique d'authentification.
     *
     * @return string
     *
     * @throws \Pollen\PwaPush\PwaPushMissingPublicKey
     */
    public function getPublicKey(): string;

    /**
     * Récupération de la clé privée d'authentification.
     *
     * @return string
     *
     * @throws \Pollen\PwaPush\PwaPushMissingPrivateKey
     */
    public function getPrivateKey(): string;

    /**
     * Définition de la clé publique d'authentification.
     *
     * @param string $publicKey
     *
     * @return static
     */
    public function setPublicKey(string $publicKey): PwaPushInterface;

    /**
     * Définition de la clé privée d'authentification.
     *
     * @param string $privateKey
     *
     * @return static
     */
    public function setPrivateKey(string $privateKey) : PwaPushInterface;
}
