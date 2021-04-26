<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use ErrorException;
use Illuminate\Database\Schema\Blueprint;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Pollen\Pwa\PwaInterface;
use Pollen\Pwa\PwaProxy;
use Pollen\PwaPush\Controller\PwaPushController;
use Pollen\PwaPush\Controller\PwaPushTestModeController;
use Pollen\PwaPush\Exception\PwaPushMissingPublicKey;
use Pollen\PwaPush\Exception\PwaPushMissingPrivateKey;
use Pollen\PwaPush\Exception\PwaPushSendNotificationError;
use Pollen\PwaPush\Exception\PwaPushSubscriptionInvalid;
use Pollen\PwaPush\Exception\PwaPushVAPIDConnexionError;
use Pollen\PwaPush\Partial\PwaPushPartial;
use Pollen\PwaPush\Middleware\PwaPushTestMiddleware;
use Pollen\Routing\RouteGroupInterface;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Concerns\ResourcesAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\DbProxy;
use Pollen\Support\Proxy\EventProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Proxy\PartialProxy;
use Pollen\Support\Proxy\RouterProxy;
use Psr\Container\ContainerInterface as Container;
use Throwable;

class PwaPush implements PwaPushInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ResourcesAwareTrait;
    use ContainerProxy;
    use DbProxy;
    use EventProxy;
    use HttpRequestProxy;
    use PartialProxy;
    use PwaProxy;
    use RouterProxy;

    /**
     * Instance principale.
     * @var static|null
     */
    private static $instance;

    /**
     * Paramètres de messages par défaut.
     * @var array
     */
    protected $defaultPayloadParams = [
        /** Requis pour un fonctionnement Desktop */
        'requireInteraction' => true,
    ];

    /**
     * Clé publique.
     * @var string
     */
    private $publicKey;

    /**
     * Clé privée
     * @var string
     */
    private $privateKey;

    /**
     * @var string url|email
     */
    private $vapidSubject;

    /**
     * Activation du mode de test.
     * @var bool
     */
    protected $testModeEnabled = false;

    /**
     * @param PwaInterface $pwa
     * @param array $config
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(PwaInterface $pwa, array $config = [], ?Container $container = null)
    {
        $this->setPwa($pwa);

        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->setResourcesBaseDir(dirname(__DIR__) . '/resources');

        if ($this->config('boot_enabled', true)) {
            $this->boot();
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Récupération de l'instance principale.
     *
     * @return static
     */
    public static function getInstance(): PwaPushInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function boot(): PwaPushInterface
    {
        if (!$this->isBooted()) {
            $this->event()->trigger('pwa_push.booting', [$this]);

            /** Partials */
            $this->partial()
                ->register(
                    'pwa-push',
                    $this->containerHas(PwaPushPartial::class)
                        ? PwaPushPartial::class : new PwaPushPartial($this, $this->partial())
                );

            /** Routage */
            // - Push Testeur
            $routeTest = $this->router()->group(
                '/api/pwa-push/test-mode',
                function (RouteGroupInterface $router) {
                    $testController = new PwaPushTestModeController($this);

                    $router->get('sw.js', [$testController, 'serviceWorker']);
                    $router->get('test-mode.styles.css', [$testController, 'globalStyles']);
                    $router->get('badge.png', [$testController, 'badgeRender']);
                    $router->get('icon.png', [$testController, 'iconRender']);

                    $router->get('tester.styles.css', [$testController, 'testerStyles']);
                    $router->get('tester.scripts.js', [$testController, 'testerScripts']);
                    $router->xhr('tester.subscription', [$testController, 'xhrSubscription']);
                    $router->xhr('tester.subscription', [$testController, 'xhrSubscription'], 'PUT');
                    $router->xhr('tester.subscription', [$testController, 'xhrSubscription'], 'DELETE');
                    $router->xhr('tester.send', [$testController, 'testerXhrSend']);
                    $router->get('tester', [$testController, 'testerRender']);

                    $router->get('notifier.styles.css', [$testController, 'notifierStyles']);
                    $router->get('notifier.scripts.js', [$testController, 'notifierScripts']);
                    $router->xhr('notifier.send', [$testController, 'notifierXhrSend']);
                    $router->get('notifier', [$testController, 'notifierRender']);

                    $router->get('messenger.styles.css', [$testController, 'messengerStyles']);
                    $router->get('messenger.scripts.js', [$testController, 'messengerScripts']);
                    $router->xhr('messenger.send', [$testController, 'messengerXhrSend']);
                    $router->get('messenger.send', [$testController, 'messengerXhrSend']);
                    $router->get('messenger', [$testController, 'messengerRender']);
                }
            );

            if ($this->getContainer()) {
                $routeTest->middle('pwa-push.test');
            } else {
                $routeTest->middleware(new PwaPushTestMiddleware($this));
            }

            $pushController = $this->containerHas(PwaPushController::class) ?
                PwaPushController::class : new PwaPushController($this);

            $this->router()->xhr('/api/pwa-push/subscription', [$pushController, 'xhrSubscription']);
            $this->router()->xhr('/api/pwa-push/subscription', [$pushController, 'xhrSubscription'], 'PUT');
            $this->router()->xhr('/api/pwa-push/subscription', [$pushController, 'xhrSubscription'], 'DELETE');

            $this->pwa()->serviceWorker()->appendScripts(
                file_get_contents($this->resources('assets/dist/js/pwa.sw.append.js'))
            );

            $this->setBooted();

            $this->event()->trigger('pwa_push.booted', [$this]);
        }

        return $this;
    }

    /**
     * Générateur de clés d'authentification VAPID.
     *
     * @return array
     *
     * @throws ErrorException
     */
    public static function generateKeys(): array
    {
        try {
            return VAPID::createVapidKeys();
        } catch (ErrorException $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function dbMigrateSubscribers(): void
    {
        $db = $this->db();

        $db->addConnection(
            array_merge($db->getConnection()->getConfig(), ['strict' => false]),
            'pwa-push.subscribers'
        );

        $schema = $db->getConnection('pwa-push.subscribers')->getSchemaBuilder();

        if (!$schema->hasTable('pwa_push_subscriber')) {
            $schema->create(
                'pwa_push_subscriber',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('auth_token', 255);
                    $table->string('content_encoding', 255);
                    $table->string('endpoint', 255);
                    $table->string('public_key', 255);
                    $table->timestamp('created_at')->nullable();
                    $table->timestamp('updated_at')->nullable();
                    $table->string('client_ip', 100);
                    $table->string('user_agent', 255);
                    $table->bigInteger('user_id')->default(0);
                    $table->index('id', 'id');
                    $table->index('user_id', 'user_id');
                }
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function dbMigrateMessages(): void
    {
        $db = $this->db();

        $db->addConnection(
            array_merge($db->getConnection()->getConfig(), ['strict' => false]),
            'pwa-push.messages'
        );

        $schema = $db->getConnection('pwa-push.messages')->getSchemaBuilder();

        if (!$schema->hasTable('pwa_push_message')) {
            $schema->create(
                'pwa_push_message',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->longText('payload');
                    $table->longText('context')->nullable();
                    $table->timestamp('created_at')->nullable();
                    $table->timestamp('updated_at')->nullable();
                    $table->bigInteger('author_id')->default(0)->index();
                    $table->timestamp('send_at')->nullable();
                    $table->index('id', 'id');
                }
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function enableTestMode(bool $testModeEnabled = true): PwaPushInterface
    {
        $this->testModeEnabled = $testModeEnabled;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultPayloadParams(): array
    {
        return $this->defaultPayloadParams ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getPublicKey(): string
    {
        if ($this->publicKey !== null) {
            return $this->publicKey;
        }
        throw new PwaPushMissingPublicKey();
    }

    /**
     * @inheritDoc
     */
    public function getPrivateKey(): string
    {
        if ($this->privateKey !== null) {
            return $this->privateKey;
        }
        throw new PwaPushMissingPrivateKey();
    }

    /**
     * @inheritDoc
     */
    public function getVAPIDSubject(): string
    {
        if ($this->vapidSubject === null) {
            $this->vapidSubject =  $this->httpRequest()->getUriForPath('');
        }

        return $this->vapidSubject;
    }

    /**
     * @inheritDoc
     */
    public function isTestModeEnabled(): bool
    {
        return $this->testModeEnabled;
    }

    /**
     * @inheritDoc
     */
    public function registerConnection(
        ?string $publicKey = null,
        ?string $privateKey = null,
        ?string $subject = null
    ): WebPush {
        $publicKey = $publicKey ?? $this->getPublicKey();
        $privateKey = $privateKey ?? $this->getPrivateKey();
        $subject = $subject ?? $this->getVAPIDSubject();

        try {
            return new WebPush(
                [
                    'VAPID' => [
                        'subject' => $subject,
                        'publicKey' => $publicKey,
                        'privateKey' => $privateKey
                    ]
                ]
            );
        } catch (ErrorException $e) {
            throw new PwaPushVAPIDConnexionError('', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function registerSubscription(array $datas): SubscriptionInterface
    {
        try {
            return Subscription::create($datas);
        } catch (ErrorException $e) {
            throw new PwaPushSubscriptionInvalid('', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function sendNotification(
        SubscriptionInterface $subscription,
        array $payloadParams = [],
        ?WebPush $connexion = null,
        array $options = []
    ): MessageSentReport {
        if ($connexion === null) {
            try {
               $connexion = $this->registerConnection();
            } catch (PwaPushVAPIDConnexionError $e) {
                throw new PwaPushSendNotificationError($e->getMessage(), 0, $e);
            }
        }

        try {
            $payload = json_encode(array_merge($this->getDefaultPayloadParams(), $payloadParams), JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new PwaPushSendNotificationError($e->getMessage(), 0, $e);
        }

        try {
            return $connexion->sendOneNotification($subscription, $payload, $options);
        } catch (ErrorException $e) {
            throw new PwaPushSendNotificationError('', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function setDefaultPayloadParams(array $defaultPayloadParams): PwaPushInterface
    {
        $this->defaultPayloadParams = $defaultPayloadParams;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPublicKey(string $publicKey): PwaPushInterface
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPrivateKey(string $privateKey): PwaPushInterface
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setVAPIDSubject(string $vapidSubject): PwaPushInterface
    {
        $this->vapidSubject = $vapidSubject;

        return $this;
    }
}
