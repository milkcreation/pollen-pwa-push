<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\WebPush;
use Pollen\Pwa\PwaProxyInterface;
use Pollen\PwaPush\Exception\PwaPushMissingPublicKey;
use Pollen\PwaPush\Exception\PwaPushMissingPrivateKey;
use Pollen\PwaPush\Exception\PwaPushSendNotificationError;
use Pollen\PwaPush\Exception\PwaPushSubscriptionInvalid;
use Pollen\PwaPush\Exception\PwaPushVAPIDConnexionError;
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
     * Récupération des paramètres de message par défaut.
     *
     * @return array
     */
    public function getDefaultPayloadParams(): array;

    /**
     * Récupération de la clé publique d'authentification.
     *
     * @return string
     *
     * @throws PwaPushMissingPublicKey
     */
    public function getPublicKey(): string;

    /**
     * Récupération de la clé privée d'authentification.
     *
     * @return string
     *
     * @throws PwaPushMissingPrivateKey
     */
    public function getPrivateKey(): string;

    /**
     * @return string
     */
    public function getVAPIDSubject(): string;

    /**
     * Vérification d'activation du mode de test.
     *
     * @return bool
     */
    public function isTestModeEnabled(): bool;

    /**
     * Déclaration d'une connexion.
     *
     * @param string|null $publicKey
     * @param string|null $privateKey
     * @param string|null $subject
     *
     * @return WebPush
     *
     * @throws PwaPushVAPIDConnexionError
     */
    public function registerConnection(
        ?string $publicKey = null,
        ?string $privateKey = null,
        ?string $subject = null
    ): WebPush;

    /**
     * Déclaration d'un abonnement.
     *
     * @param array $datas
     *
     * @return SubscriptionInterface
     *
     * @throws PwaPushSubscriptionInvalid
     */
    public function registerSubscription(array $datas): SubscriptionInterface;

    /**
     * Envoi d'un message de notification
     *
     * @param SubscriptionInterface $subscription
     * @param array $payloadParams
     * @param WebPush|null $connexion
     * @param array $options
     *
     * @return MessageSentReport
     *
     * @throws PwaPushSendNotificationError
     */
    public function sendNotification(
        SubscriptionInterface $subscription,
        array $payloadParams = [],
        WebPush $connexion = null,
        array $options = []
    ): MessageSentReport;

    /**
     * Définition des paramètres de message par défaut.
     *
     * @param array $defaultPayloadParams
     *
     * @return static
     */
    public function setDefaultPayloadParams(array $defaultPayloadParams): PwaPushInterface;

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

    /**
     * @return static
     */
    public function setVAPIDSubject(string $vapidSubject): PwaPushInterface;
}
