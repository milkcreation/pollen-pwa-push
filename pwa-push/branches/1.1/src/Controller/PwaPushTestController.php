<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Controller;

use League\Route\Http\Exception\ForbiddenException;
use Pollen\Http\JsonResponseInterface;
use Pollen\Http\ResponseInterface;
use Pollen\Http\UrlHelper;
use Throwable;

class PwaPushTestController extends AbstractPwaPushController
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publicKey = file_get_contents($this->pwaPush->resources('/keys/test.public_key.txt'));
        $this->privateKey = file_get_contents($this->pwaPush->resources('/keys/test.private_key.txt'));
        $this->payloadParams = [
            'title'              => 'Pwa Push Test >> Ok !!',
            'body'               => 'Hello World ! 👋',
            'badge'              => (new UrlHelper())->getAbsoluteUrl('api/pwa-push/test/badge.png'),
            'icon'               => (new UrlHelper())->getAbsoluteUrl('api/pwa-push/test/icon.png'),
            /** @see https://appmakers.dev/bcp-47-language-codes-list/ */
            'lang'               => 'fr-FR',
            'requireInteraction' => true,
            'silent'             => false,
            'tag'                => md5('pwa-push' . uniqid('', true)),
            'vibrate'            => 300,
        ];
    }

    /**
     * Styles CSS de la page de test.
     *
     * @return ResponseInterface
     */
    public function cssStyles(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/css/test.styles.css'));

        return $this->response($content, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * Rendu du badge de message de notification.
     *
     * @return ResponseInterface
     */
    public function badgeRender(): ResponseInterface
    {
        return $this->file($this->pwaPush->resources('/assets/dist/img/badge.png'), null, 'inline');
    }

    /**
     * Rendu HTML de la page de test.
     *
     * @return ResponseInterface
     */
    public function htmlRender(): ResponseInterface
    {
        $this->params(
            [
                'PushTest' => [
                    'l10n'       => [
                        'button_default'  => 'Activer/Désactiver',
                        'sending'         => 'Envoyer',
                        'enabled'         => 'Désactiver',
                        'disabled'        => 'Activer',
                        'computing'       => 'Chargement...',
                        'incompatible'    => 'Indisponible depuis ce navigateur',
                        'please_enabling' => 'Veuillez d\'abord activer les notifications !',
                    ],
                    'public_key' => $this->publicKey,
                ],
            ]
        );

        return $this->view('test', $this->params()->all());
    }

    /**
     * Rendu de l'icône de message de notification.
     *
     * @return ResponseInterface
     */
    public function iconRender(): ResponseInterface
    {
        return $this->file($this->pwaPush->resources('/assets/dist/img/icon.png'), null, 'inline');
    }

    /**
     * Scripts JS de la page de test.
     *
     * @return ResponseInterface
     */
    public function jsScripts(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/js/test.scripts.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Service Worker de la page de test.
     *
     * @return ResponseInterface
     */
    public function serviceWorker(): ResponseInterface
    {
        $content = file_get_contents($this->pwaPush->resources('/assets/dist/js/test.service-worker.js'));

        return $this->response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Requête HTTP XHR de traitement de l'envoi de message.
     *
     * @return JsonResponseInterface
     *
     * @throws ForbiddenException
     */
    public function xhrSend(): JsonResponseInterface
    {
        try {
            $datas = json_decode($this->httpRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ForbiddenException($e->getMessage());
        }

        try {
            $report = $this->send($datas);

            return $this->json(
                [
                    'success' => true,
                    'data'    => $report,
                ]
            );
        } catch (Throwable $e) {
            throw new ForbiddenException($e->getMessage());
        }
    }
}
