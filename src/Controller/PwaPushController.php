<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use Carbon\Carbon;
use Pollen\Http\JsonResponseInterface;
use Pollen\PwaPush\Eloquent\Subscriber;
use Pollen\Support\Proxy\SessionProxy;
use Pollen\Routing\Exception\ForbiddenException;

class PwaPushController extends AbstractPwaPushController
{
    use SessionProxy;

    /**
     * Requête HTTP XHR de traitement de l'abonnement aux notifications.
     *
     * @return JsonResponseInterface
     *
     * @throws ForbiddenException
     */
    public function xhrSubscription(): JsonResponseInterface
    {
        $token = $this->httpRequest()->input()->pull('token');

        if (!$this->session()->verifyToken($token)) {
            throw new ForbiddenException(
                'PwaPush: CSRF Protection invalid',
                'PwaPush Error'
            );
        }

        return parent::xhrSubscription();
    }

    /**
     * @inheritDoc
     */
    protected function subscriptionCreate(array $subscription, int $user_id = 0): array
    {
        Subscriber::on()->insert(
            [
                'auth_token'       => $subscription['authToken'],
                'content_encoding' => $subscription['contentEncoding'],
                'endpoint'         => $subscription['endpoint'],
                'public_key'       => $subscription['publicKey'],
                'created_at'       => Carbon::now(),
                'client_ip'        => $this->httpRequest()->getClientIp(),
                'user_agent'       => $this->httpRequest()->getUserAgent(),
                'user_id'          => $user_id,
            ]
        );

        return [
            'success' => true,
            'message' => 'PwaPush: Subscription created.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function subscriptionDelete(array $subscription, int $user_id = 0): array
    {
        Subscriber::on()->where(
            [
                'endpoint' => $subscription['endpoint'],
            ]
        )->delete();

        return [
            'success' => true,
            'message' => 'PwaPush: Subscription deleted.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function subscriptionUpdate(array $subscription, int $user_id = 0): array
    {
        $subscriber = Subscriber::on()->where('endpoint', $subscription['endpoint'])->first();

        if ($subscriber) {
            $userID = empty($user_id) && !empty($subscriber->user_id) ? $subscriber->user_id : $user_id;
            $subscriber->update(
                [
                    'auth_token'       => $subscription['authToken'],
                    'content_encoding' => $subscription['contentEncoding'],
                    'public_key'       => $subscription['publicKey'],
                    'updated_at'       => Carbon::now(),
                    'client_ip'        => $this->httpRequest()->getClientIp(),
                    'user_agent'       => $this->httpRequest()->getUserAgent(),
                    'user_id'          => $userID,
                ]
            );

            return [
                'success' => true,
                'message' => 'PwaPush: Subscription updated.',
                'data'    => compact('subscription', 'user_id'),
            ];
        }

        Subscriber::on()->create(
            [
                'auth_token'       => $subscription['authToken'],
                'content_encoding' => $subscription['contentEncoding'],
                'endpoint'         => $subscription['endpoint'],
                'public_key'       => $subscription['publicKey'],
                'updated_at'       => Carbon::now(),
                'client_ip'        => $this->httpRequest()->getClientIp(),
                'user_agent'       => $this->httpRequest()->getUserAgent(),
                'user_id'          => $user_id,
            ]
        );

        return [
            'success' => false,
            'message' => 'PwaPush: Subscription could not updated.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }
}
