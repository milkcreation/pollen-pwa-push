<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use Pollen\Http\JsonResponseInterface;
use Pollen\Http\ResponseInterface;
use Pollen\Http\UrlHelper;
use Pollen\Routing\Exception\BadRequestException;
use Pollen\Routing\Exception\ForbiddenException;
use Pollen\PwaPush\Eloquent\Message;
use Pollen\PwaPush\Eloquent\Subscriber;
use Throwable;

class PwaPushTestModeController extends AbstractPwaPushController
{
    /**
     * Styles CSS globaux.
     *
     * @return ResponseInterface
     */
    public function globalStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/css/test-mode.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Rendu du badge de message de notification en mode test.
     *
     * @return ResponseInterface
     */
    public function badgeRender(): ResponseInterface
    {
        return $this->file($this->pwaPush()->resources('/assets/dist/img/test-mode.badge.png'), null, 'inline');
    }

    /**
     * Rendu de l'icône de message de notification en mode test.
     *
     * @return ResponseInterface
     */
    public function iconRender(): ResponseInterface
    {
        return $this->file($this->pwaPush()->resources('/assets/dist/img/test-mode.icon.png'), null, 'inline');
    }

    /**
     * Service Worker du mode test.
     *
     * @return ResponseInterface
     */
    public function serviceWorker(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/js/test-mode.sw.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Styles CSS de la page de messager.
     *
     * @return ResponseInterface
     */
    public function messengerStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/css/test-mode.messenger.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Scripts JS de la page du messager.
     *
     * @return ResponseInterface
     */
    public function messengerScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/js/test-mode.messenger.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Rendu HTML de la page du messager.
     *
     * @return ResponseInterface
     */
    public function messengerRender(): ResponseInterface
    {
        $messages = Message::on()->get();

        $messageCount = count($messages);
        $subscriberCount = Subscriber::on()->count();

        $this->datas(
            [
                'PwaPushMessenger' => [
                    'l10n'     => [
                        'title'  => 'Messages aux abonnés',
                        'text'   => 'Tester l\'envoi des messages aux abonnés.',
                        'infos'  => 'infos : ' . sprintf(
                                ($messageCount > 1 ? '%d messages' : '%d message') .
                                ' - ' .
                                ($subscriberCount > 1 ? '%d abonnés' : '%d abonné'),
                                $messageCount,
                                $subscriberCount
                            ),
                        'table'  => [
                            'title' => 'Liste des messages',
                            'head'  => [
                                'message'    => 'Message',
                                'created_at' => 'Crée le',
                            ],
                        ],
                        'button' => [
                            'text' => 'Envoyer',
                        ],
                        'error' => [
                            'missing' => 'Veuillez d\'abord sélectionner le message à envoyer.'
                        ]
                    ],
                    'messages' => [
                        'count' => $messageCount,
                        'datas' => $messages,
                    ],
                ],
            ]
        );

        return $this->view('messenger');
    }

    /**
     * Requête HTTP XHR d'envoi de message de notification aux abonnés via le messager.
     *
     * @return JsonResponseInterface
     *
     * @throws BadRequestException
     */
    public function messengerXhrSend(): JsonResponseInterface
    {
        $message = Message::on()->where('id', $this->httpRequest()->input('message_id'))->first();

        if ($message) {
            $subscribers = Subscriber::on()->get();

            $reports = [];
            /** @var Subscriber $subscriber */
            foreach ($subscribers as $subscriber) {
                try {
                    $reports[] = $this->send(
                        $subscriber->subscription,
                        array_merge($this->payloadParams, $message->payload)
                    );
                } catch (Throwable $e) {
                    unset($e);
                }
            }

            return $this->json($reports);
        }

        throw new BadRequestException('Message unavailable');
    }

    /**
     * Styles CSS de la page de notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/css/test-mode.notifier.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Scripts JS de la page du notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/js/test-mode.notifier.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Rendu HTML de la page du notificateur.
     *
     * @return ResponseInterface
     */
    public function notifierRender(): ResponseInterface
    {
        $subscriberCount = Subscriber::on()->count();

        $this->datas(
            [
                'PwaPushNotifier' => [
                    'l10n' => [
                        'title'  => 'Notification aux abonnés',
                        'text'   => 'Tester l\'envoi d\'un message de notification aux abonnés.',
                        'infos'  => 'infos : ' . sprintf(
                                ($subscriberCount > 1 ? '%d abonnés' : '%d abonné'),
                                $subscriberCount
                            ),
                        'form'   => [
                            'title' => 'Composition du message',
                        ],
                        'fields' => [
                            'title' => [
                                'placeholder' => 'Titre du message',
                                'value'       => 'PwaPush - Notifier test',
                            ],
                            'body'  => [
                                'placeholder' => 'Texte du message',
                                'value'       => 'Yeah baby ! ✌',
                            ],
                        ],
                        'submit' => [
                            'text' => 'Envoyer',
                        ],
                    ],
                ],
            ]
        );

        return $this->view('notifier');
    }

    /**
     * Requête HTTP XHR d'envoi de message de notification aux abonnés via le notificateur.
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
            } catch (Throwable $e) {
                unset($e);
            }
        }

        return $this->json(
            [
                'success' => true,
                'data'    => $reports,
            ]
        );
    }

    /**
     * Styles CSS de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/css/test-mode.tester.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Scripts JS de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush()->resources('/assets/dist/js/test-mode.tester.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Rendu HTML de la page du testeur de push.
     *
     * @return ResponseInterface
     */
    public function testerRender(): ResponseInterface
    {
        $this->datas(
            [
                'PwaPushTester' => [
                    'l10n'       => [
                        'title'           => 'Test de notification',
                        'text'            => 'Tester l\'abonnement et l\'envoi d\'un message de notification.',
                        'button_default'  => 'Activer/Désactiver',
                        'sending'         => 'Envoyer',
                        'enabled'         => 'Désactiver',
                        'disabled'        => 'Activer',
                        'computing'       => 'Chargement...',
                        'incompatible'    => 'Indisponible depuis ce navigateur',
                        'please_enabling' => 'Veuillez d\'abord activer les notifications !',
                    ],
                    'public_key' => file_get_contents($this->pwaPush()->resources('/keys/tester.public_key.txt')),
                ],
            ]
        );

        return $this->view('tester');
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
        $datas = $this->httpRequest()->input()->all();

        try {
            $this->publicKey = file_get_contents($this->pwaPush()->resources('/keys/tester.public_key.txt'));
            $this->privateKey = file_get_contents($this->pwaPush()->resources('/keys/tester.private_key.txt'));
            $this->payloadParams = [
                'title'              => 'PwaPush - Test is Ok',
                'body'               => 'Hello World ! 👋',
                'badge'              => (new UrlHelper())->getAbsoluteUrl('api/pwa-push/test-mode/badge.png'),
                'icon'               => (new UrlHelper())->getAbsoluteUrl('/api/pwa-push/test-mode/icon.png'),
                'lang'               => 'fr-FR',
                'requireInteraction' => true,
                'silent'             => false,
                'tag'                => md5('pwa-push' . uniqid('', true)),
                'vibrate'            => 300,
            ];

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

    /**
     * @inheritDoc
     */
    public function viewEngineDirectory(): string
    {
        return $this->pwaPush()->resources('/views/mode-test');
    }
}
