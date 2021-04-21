<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use Carbon\Carbon;
use Pollen\Http\JsonResponseInterface;
use Pollen\Http\ResponseInterface;
use Pollen\PwaPush\Eloquent\Subscriber;

class PwaPushController extends AbstractPwaPushController
{
    /**
     * @return ResponseInterface
     */
    public function notifier(): ResponseInterface
    {
        return $this->view('notifier');
    }

    /**
     * @return JsonResponseInterface
     */
    public function notifierSend(): JsonResponseInterface
    {
        $subscribers = Subscriber::on()->get();

        $reports = [];
        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $reports[] = $this->send(
                $subscriber->subscription,
                [
                    'title'              => 'Pwa Push Test >> Ok !!',
                    'body'               => 'Hello World ! 👋',
                    /** @see https://appmakers.dev/bcp-47-language-codes-list/ */
                    'lang'               => 'fr-FR',
                    'requireInteraction' => true,
                    'silent'             => false,
                    'tag'                => md5('pwa-push' . uniqid('', true)),
                    'vibrate'            => 300,
                ]
            );
        }

        return $this->json(['success' => true, 'data' => $reports]);
    }

    /**
     * Service Worker de la page de test.
     *
     * @return ResponseInterface
     */
    public function serviceWorker(): ResponseInterface
    {
        $content = 'self.addEventListener("push", function (event) {
            console.log("push#2")
          if (!(self.Notification && self.Notification.permission === "granted")) {
            return
          }
            console.log("push#3")
          const sendNotification = (data) => {
            /**
             * @param {Object} jsonData
             * @param {string} jsonData.body
             */
            let jsonData = JSON.parse(data),
                title = jsonData.title
        
            delete jsonData.title
        
            return self.registration.showNotification(title, jsonData)
          }
        
          if (event.data) {
            const message = event.data.text()
            event.waitUntil(sendNotification(message))
          }
        })';

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
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
        Subscriber::on()->where(
            [
                'public_key' => $subscription['publicKey'],
            ]
        )->update(
            [
                'auth_token'       => $subscription['authToken'],
                'content_encoding' => $subscription['contentEncoding'],
                'endpoint'         => $subscription['endpoint'],
                'updated_at'       => Carbon::now(),
                'user_id'          => $user_id,
            ]
        );

        return [
            'success' => true,
            'message' => 'PwaPush: Subscription updated.',
            'data'    => compact('subscription', 'user_id'),
        ];
    }
}