<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Partial;

use Pollen\Http\UrlHelper;
use Pollen\Partial\PartialDriver;
use Pollen\Partial\PartialManagerInterface;
use Pollen\PwaPush\PwaPushInterface;
use Pollen\PwaPush\PwaPushProxy;
use Pollen\Support\Proxy\SessionProxy;
use Throwable;

class PwaPushPartial extends PartialDriver
{
    use PwaPushProxy;
    use SessionProxy;

    /**
     * @param PwaPushInterface $pwaPush
     * @param PartialManagerInterface $partialManager
     */
    public function __construct(PwaPushInterface $pwaPush, PartialManagerInterface $partialManager)
    {
        $this->setPwaPush($pwaPush);

        parent::__construct($partialManager);
    }

    /**
     * @inheritDoc
     */
    public function defaultParams(): array
    {
        return array_merge(
            parent::defaultParams(),
            [
                /**
                 * Url vers la requête HTTP XHR d'abonnement aux notifications (requis)
                 * @var string
                 */
                'endpoint'   => '',
                /**
                 * Clé publique d'authentification au service (requise)
                 * @var string
                 */
                'public_key' => '',
                /**
                 * Identifiant de qualification de l'utilisateur associé.
                 * @var int
                 */
                'user_id'    => 0,
                'classes'    => [
                    'title'   => 'PwaPush-title',
                    'content' => 'PwaPush-content',
                    'close'   => 'PwaPush-close',
                    'switch'  => 'PwaPush-switch',
                    'handler' => 'PwaPush-handler',
                ],
                /**
                 * @var string fixed|fixed-bottom
                 */
                'style'      => 'fixed',
                'title'      => 'Activer les notifications',
                'content'    => 'L\'activation des notifications permet de rester informé des dernières nouveautés de' .
                    ' l\'application.',
                'close'      => '&#x2715;',
                'timeout'    => 5000,
                'handler'    => file_get_contents($this->pwaPush()->resources('assets/dist/img/bell-ico.svg')),
                'observe'    => true,
            ]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Throwable
     */
    public function render(): string
    {
        $defaultClasses = [
            'title'   => 'PwaPush-title',
            'content' => 'PwaPush-content',
            'close'   => 'PwaPush-close',
            'switch'  => 'PwaPush-switch',
            'handler' => 'PwaPush-handler',
        ];

        foreach ($defaultClasses as $key => $class) {
            $this->set("classes.$key", sprintf($this->get("classes.$key", '%s'), $class));
        }

        $this->set(
            [
                'attrs.id'            => 'PwaPush',
                'attrs.class'         => sprintf(
                    '%s PwaPush--' . $this->get('style'),
                    $this->get('attrs.class')
                ),
                'attrs.data-pwa-push' => 'banner',
            ]
        );

        if ($this->get('observe')) {
            $this->set('attrs.data-observe', 'pwa-push');
        }

        $timeout = $this->get('timeout');
        if (is_numeric($timeout) && $timeout >= 1000) {
            $this->set('attrs.data-timeout', $timeout);
        }

        if (!$this->get('endpoint')) {
            $this->set('endpoint', (new UrlHelper())->getRelativePath('/api/pwa-push/subscription'));
        }

        if (!$this->get('public_key')) {
            try {
                $this->set('public_key', $this->pwaPush()->getPublicKey());
            } catch (Throwable $e) {
                throw $e;
            }
        }

        $this->set(
            [
                'attrs.data-options' => [
                    'endpoint'   => $this->get('endpoint'),
                    'public_key' => $this->get('public_key'),
                    'user_id'    => $this->get('user_id'),
                    'token'      => $this->session()->getToken()
                ],
            ]
        );

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function viewDirectory(): string
    {
        return $this->pwaPush()->resources('/views/partial/pwa-push');
    }
}
