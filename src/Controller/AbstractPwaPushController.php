<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use League\Route\Http\Exception\ForbiddenException;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Pollen\Http\JsonResponseInterface;
use Pollen\Routing\BaseViewController;
use Pollen\PwaPush\PwaPushInterface;
use Psr\Container\ContainerInterface as Container;
use Throwable;

abstract class AbstractPwaPushController extends BaseViewController
{
    /**
     * Attributs de message.
     * @see https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
     *
     * @var array
     */
    protected $payloadParams = [];

    /**
     * @var PwaPushInterface
     */
    protected $pwaPush;

    /**
     * Clé publique.
     * @var string
     */
    protected $publicKey;

    /**
     * Clé privée
     * @var string
     */
    protected $privateKey;

    /**
     * @param PwaPushInterface $pwaPush
     * @param Container|null $container
     */
    public function __construct(PwaPushInterface $pwaPush, ?Container $container = null)
    {
        $this->pwaPush = $pwaPush;

        parent::__construct($container);
    }

    /**
     * Récupération de la liste des paramètres de message de notification.
     *
     * @return array
     */
    protected function getPayloadParams(): array
    {
        return $this->payloadParams;
    }

    /**
     * Récupération de la clé publique.
     *
     * @return string
     */
    protected function publicKey(): string
    {
        if ($this->publicKey === null) {
            $this->publicKey = $this->pwaPush->getPublicKey();
        }
        return $this->publicKey;
    }

    /**
     * Récupération de la clé privée.
     *
     * @return string
     */
    protected function privateKey(): string
    {
        if ($this->privateKey === null) {
            $this->privateKey = $this->pwaPush->getPrivateKey();
        }
        return $this->privateKey;
    }

    /**
     * Envoi d'un message de notification
     *
     * @param array $datas Données d'abonnement.
     * @param array $params Paramètres du message.
     * @param array $options Options d'expédition.
     *
     * @return MessageSentReport
     *
     * @throws Throwable
     */
    protected function send(array $datas, array $params = [], array $options = []): MessageSentReport
    {
        try {
            $subscription = Subscription::create($datas);
        } catch (Throwable $e) {
            throw $e;
        }

        try {
            $publicKey = $this->publicKey();
            $privateKey = $this->privateKey();

            $webPush = new WebPush(
                [
                    'VAPID' => [
                        'subject'    => $this->httpRequest()->getUriForPath(''),
                        'publicKey'  => $publicKey,
                        'privateKey' => $privateKey,
                    ],
                ]
            );
        } catch (Throwable $e) {
            throw $e;
        }

        try {
            $payload = json_encode(array_merge($this->getPayloadParams(), $params), JSON_THROW_ON_ERROR);
            $options = array_merge([], $options);

            return $webPush->sendOneNotification($subscription, $payload, $options);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Définition de la liste des paramètres de message de notification.
     *
     * @param array $payloadParams
     *
     * @return self
     */
    public function setPayloadParams(array $payloadParams): self
    {
        $this->payloadParams = $payloadParams;

        return $this;
    }

    /**
     * Création d'un abonnement aux messages de notifications.
     *
     * @param array $subscription
     * @param int $user_id
     *
     * @return array
     */
    protected function subscriptionCreate(array $subscription, int $user_id = 0): array
    {
        return [
            'success' => true,
            'message' => 'PwaPush: Subscription created.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }

    /**
     * Suppression d'un abonnement aux messages de notifications.
     *
     * @param array $subscription
     * @param int $user_id
     *
     * @return array
     */
    protected function subscriptionDelete(array $subscription, int $user_id = 0): array
    {
        return [
            'success' => true,
            'message' => 'PwaPush: Subscription deleted.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }

    /**
     * Mise à jour d'un abonnement aux messages de notifications.
     *
     * @param array $subscription
     * @param int $user_id
     *
     * @return array
     */
    protected function subscriptionUpdate(array $subscription, int $user_id = 0): array
    {
        return [
            'success' => true,
            'message' => 'PwaPush: Subscription updated.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }

    /**
     * Requête HTTP XHR de traitement de l'abonnement aux notifications.
     *
     * @return JsonResponseInterface
     *
     * @throws ForbiddenException
     */
    public function xhrSubscription(): JsonResponseInterface
    {
        try {
            $subscription = json_decode($this->httpRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($subscription['endpoint'])) {
                throw new ForbiddenException(
                    'PwaPush: Subscription Invalid >> Endpoint subscription missing'
                );
            }
        } catch (Throwable $e) {
            throw new ForbiddenException('PwaPush: Subscription is invalid');
        }

        switch ($method = $this->httpRequest()->getMethod()) {
            case 'POST':
                return $this->json($this->subscriptionCreate($subscription));
            case 'DELETE':
                return $this->json($this->subscriptionDelete($subscription));
            case 'PUT':
                return $this->json($this->subscriptionUpdate($subscription));
            default:
                throw new ForbiddenException(
                    sprintf('PwaPush: Subscription HTTP request method [%s] not handled', $method)
                );
        }
    }

    /**
     * @inheritDoc
     */
    public function viewEngineDirectory(): string
    {
        return $this->pwaPush->resources('/views');
    }
}
