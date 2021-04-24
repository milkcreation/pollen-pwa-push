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
     * Création de la table de base de données des abonnés.
     *
     * @return void
     */
    public function dbMigrateSubscribers(): void;

    /**
     * Création de la table de base de données des messages.
     *
     * @return void
     */
    public function dbMigrateMessages(): void;

    /**
     * Activation du mode de test.
     *
     * @param bool $testModeEnabled
     *
     * @return static
     */
    public function enableTestMode(bool $testModeEnabled = true): PwaPushInterface;

    /**
     * Récupération de la clé publique d'authentification.
     *
     * @return string
     *
     * @throws \Pollen\PwaPush\Exception\PwaPushMissingPublicKey
     */
    public function getPublicKey(): string;

    /**
     * Récupération de la clé privée d'authentification.
     *
     * @return string
     *
     * @throws \Pollen\PwaPush\Exception\PwaPushMissingPrivateKey
     */
    public function getPrivateKey(): string;

    /**
     * Vérification d'activation du mode de test.
     *
     * @return bool
     */
    public function isTestModeEnabled(): bool;

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
