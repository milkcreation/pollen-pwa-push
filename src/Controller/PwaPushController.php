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
                'public_key' => $subscription['publicKey'],
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
        $updated = 0;

        if ($user_id) {
            $updated = Subscriber::on()->where(
                [
                    'user_id' => $user_id,
                ]
            )->update(
                [
                    'auth_token'       => $subscription['authToken'],
                    'content_encoding' => $subscription['contentEncoding'],
                    'endpoint'         => $subscription['endpoint'],
                    'public_key'       => $subscription['publicKey'],
                    'updated_at'       => Carbon::now(),
                    'user_id'          => $user_id,
                ]
            );
        }

        if (!$updated) {
            Subscriber::on()->where(
                [
                    'public_key' => $subscription['publicKey'],
                ]
            )->updateOrCreate(
                [
                    'auth_token'       => $subscription['authToken'],
                    'content_encoding' => $subscription['contentEncoding'],
                    'endpoint'         => $subscription['endpoint'],
                    'public_key'       => $subscription['publicKey'],
                    'updated_at'       => Carbon::now(),
                    'user_id'          => $user_id,
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'PwaPush: Subscription updated.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }
}
