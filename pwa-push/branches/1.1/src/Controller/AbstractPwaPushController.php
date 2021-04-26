<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use Minishlink\WebPush\MessageSentReport;
use Pollen\Http\JsonResponseInterface;
use Pollen\PwaPush\Exception\PwaPushSubscriptionInvalid;
use Pollen\PwaPush\Exception\PwaPushVAPIDConnexionError;
use Pollen\PwaPush\PwaPushProxy;
use Pollen\Routing\BaseViewController;
use Pollen\Routing\Exception\ForbiddenException;
use Pollen\PwaPush\PwaPushInterface;
use Psr\Container\ContainerInterface as Container;
use Throwable;

abstract class AbstractPwaPushController extends BaseViewController
{
    use PwaPushProxy;

    /**
     * Attributs de message.
     * @see https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
     * @var array
     */
    protected $payloadParams = [];

    /**
     * Clé publique.
     * @var string|null
     */
    protected $publicKey;

    /**
     * Clé privée
     * @var string|null
     */
    protected $privateKey;

    /**
     * @var string|null
     */
    protected $vapidSubject;

    /**
     * @param PwaPushInterface|null $pwaPush
     * @param Container|null $container
     */
    public function __construct(?PwaPushInterface $pwaPush = null, ?Container $container = null)
    {
        if ($pwaPush !== null) {
            $this->setPwaPush($pwaPush);
        }

        parent::__construct($container);
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
     * @throws PwaPushSubscriptionInvalid
     * @throws Throwable
     */
    protected function send(array $datas, array $params = [], array $options = []): MessageSentReport
    {
        try {
            $subscription = $this->pwaPush()->registerSubscription($datas);
        } catch (PwaPushSubscriptionInvalid $e) {
            throw $e;
        }

        try {
            $connexion = $this->pwaPush()->registerConnection($this->publicKey, $this->privateKey, $this->vapidSubject);
        } catch (PwaPushVAPIDConnexionError $e) {
            throw $e;
        }

        try {
            $payloadParams = array_merge($this->payloadParams, $params);

            return $this->pwaPush()->sendNotification($subscription, $payloadParams, $connexion, $options);
        } catch (Throwable $e) {
            throw $e;
        }
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
        $user_id = $this->httpRequest()->input()->pull('userID') ?: 0;
        $subscription = $this->httpRequest()->input()->all();

        if (!isset($subscription['endpoint'])) {
            throw new ForbiddenException(
                'PwaPush: Subscription Invalid >> Endpoint subscription missing',
                'PwaPush Error'
            );
        }

        switch ($method = $this->httpRequest()->getMethod()) {
            case 'POST':
                return $this->json($this->subscriptionCreate($subscription, $user_id));
            case 'DELETE':
                return $this->json($this->subscriptionDelete($subscription, $user_id));
            case 'PUT':
                return $this->json($this->subscriptionUpdate($subscription, $user_id));
            default:
                throw new ForbiddenException(
                    sprintf('PwaPush: Subscription HTTP request method [%s] not handled', $method),
                    'PwaPush Error'
                );
        }
    }

    /**
     * @inheritDoc
     */
    public function viewEngineDirectory(): string
    {
        return $this->pwaPush()->resources('/views');
    }
}
