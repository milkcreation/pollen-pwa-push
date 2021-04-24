<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use Pollen\Http\JsonResponseInterface;
use Pollen\Http\ResponseInterface;
use Pollen\Http\UrlHelper;
use Pollen\Routing\Exception\ForbiddenException;
use Pollen\PwaPush\Eloquent\Subscriber;
use Throwable;

class PwaPushTestController extends AbstractPwaPushController
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->payloadParams = [
            'title'              => 'PwaPush - Test is Ok',
            'body'               => 'Hello World ! 👋',
            'badge'              => (new UrlHelper())->getAbsoluteUrl('api/pwa-push/test-mode/badge.png'),
            'icon'               => (new UrlHelper())->getAbsoluteUrl('/api/pwa-push/test-mode/icon.png'),
            /** @see https://appmakers.dev/bcp-47-language-codes-list/ */
            'lang'               => 'fr-FR',
            'requireInteraction' => true,
            'silent'             => false,
            'tag'                => md5('pwa-push' . uniqid('', true)),
            'vibrate'            => 300,
        ];
    }

    /**
     * Rendu du badge de message de notification en mode test.
     *
     * @return ResponseInterface
     */
    public function badgeRender(): ResponseInterface
    {
        return $this->file($this->pwaPush->resources('/assets/dist/img/test-mode.badge.png'), null, 'inline');
    }

    /**
     * Rendu de l'icône de message de notification en mode test.
     *
     * @return ResponseInterface
     */
    public function iconRender(): ResponseInterface
    {
        return $this->file($this->pwaPush->resources('/assets/dist/img/test-mode.icon.png'), null, 'inline');
    }

    /**
     * Service Worker du mode test.
     *
     * @return ResponseInterface
     */
    public function serviceWorker(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/js/test-mode.sw.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Styles CSS de la page de notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/css/notifier.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Scripts JS de la page du notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/js/notifier.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Rendu HTML de la page du notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierRender(): ResponseInterface
    {
        $this->params(
            [
                'PwaPushNotifier' => [
                    'l10n'       => [
                        'title'  => 'Notificateur de push abonnés',
                        'fields' =>  [
                            'title'  => [
                                'placeholder' => 'Titre du message',
                                'value' => 'PwaPush - Notifier test'
                            ],
                            'body' => [
                                'placeholder' => 'Texte du message',
                                'value' => 'Yeah baby ! ✌'
                            ]
                        ]
                    ]
                ],
            ]
        );

        return $this->view('notifier', $this->params()->all());
    }

    /**
     * Requête HTTP XHR d'envoie de message de notification aux abonnés via le notificateur.
     *
     * @return JsonResponseInterface
     */
    public function notifierXhrSend(): JsonResponseInterface
    {
        $subscribers = Subscriber::on()->get();

        $reports = [];
        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            try {
                $reports[] = $this->send(
                    $subscriber->subscription,
                    array_merge($this->payloadParams, $this->httpRequest()->input()->all())
                );
            } catch(Throwable $e) {
                unset($e);
            }
        }

        return $this->json([
            'success' => true,
            'data' => $reports
       ]);
    }

    /**
     * Styles CSS de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/css/tester.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Scripts JS de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/js/tester.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Rendu HTML de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerRender(): ResponseInterface
    {
        $this->params(
            [
                'PwaPushTester' => [
                    'l10n'       => [
                        'title'           => 'Testeur de notifications Push',
                        'button_default'  => 'Activer/Désactiver',
                        'sending'         => 'Envoyer',
                        'enabled'         => 'Désactiver',
                        'disabled'        => 'Activer',
                        'computing'       => 'Chargement...',
                        'incompatible'    => 'Indisponible depuis ce navigateur',
                        'please_enabling' => 'Veuillez d\'abord activer les notifications !',
                    ],
                    'public_key' => file_get_contents($this->pwaPush->resources('/keys/tester.public_key.txt')),
                ],
            ]
        );

        return $this->view('tester', $this->params()->all());
    }

    /**
     * Requête HTTP XHR de traitement de l'envoi de message de notification du testeur de push.
     *
     * @return JsonResponseInterface
     *
     * @throws ForbiddenException
     */
    public function testerXhrSend(): JsonResponseInterface
    {
        try {
            $datas = json_decode($this->httpRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ForbiddenException(
                $e->getMessage(),
                'PwaPush Test Error',
                $e
            );
        }

        try {
            $this->publicKey = file_get_contents($this->pwaPush->resources('/keys/tester.public_key.txt'));
            $this->privateKey = file_get_contents($this->pwaPush->resources('/keys/tester.private_key.txt'));

            $report = $this->send($datas);

            return $this->json(
                [
                    'success' => true,
                    'data'    => $report,
                ]
            );
        } catch (Throwable $e) {
            throw new ForbiddenException(
                $e->getMessage(),
                'PwaPush Test Error',
                $e
            );
        }
    }
}
