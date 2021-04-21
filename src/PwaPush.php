<?php

declare(strict_types=1);

namespace Pollen\PwaPush;

use ErrorException;
use Illuminate\Database\Schema\Blueprint;
use Minishlink\WebPush\VAPID;
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
use Pollen\Pwa\PwaInterface;
use Pollen\Pwa\PwaProxy;
use Pollen\PwaPush\Controller\PwaPushController;
use Pollen\PwaPush\Controller\PwaPushTestController;
use Pollen\PwaPush\Exception\PwaPushMissingPublicKey;
use Pollen\PwaPush\Exception\PwaPushMissingPrivateKey;
use Pollen\PwaPush\Partial\PwaPushPartial;
use Psr\Container\ContainerInterface as Container;

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
            $routeTest = $this->router()->group('/api/pwa-push/test-mode', function (RouteGroupInterface $router) {
                $pushTestController = new PwaPushTestController($this);

                $router->get('sw.js', [$pushTestController, 'serviceWorker']);
                $router->get('badge.png', [$pushTestController, 'badgeRender']);
                $router->get('icon.png', [$pushTestController, 'iconRender']);

                $router->get('tester.styles.css', [$pushTestController, 'testerStyles']);
                $router->get('tester.scripts.js', [$pushTestController, 'testerScripts']);
                $router->xhr('tester.subscription', [$pushTestController, 'xhrSubscription']);
                $router->xhr('tester.subscription', [$pushTestController, 'xhrSubscription'], 'PUT');
                $router->xhr('tester.subscription', [$pushTestController, 'xhrSubscription'], 'DELETE');
                $router->xhr('tester.send', [$pushTestController, 'testerXhrSend']);
                $router->get('tester', [$pushTestController, 'testerRender']);

                $router->get('notifier.styles.css', [$pushTestController, 'notifierStyles']);
                $router->get('notifier.scripts.js', [$pushTestController, 'notifierScripts']);
                $router->xhr('notifier.send', [$pushTestController, 'notifierXhrSend']);
                $router->get('notifier', [$pushTestController, 'notifierRender']);
            });

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
    public function dbMigrate(): void
    {
        $db = $this->db();

        $db->addConnection(
            array_merge($db->getConnection()->getConfig(), ['strict' => false]),
            'pwa-push'
        );

        $schema = $db->getConnection('pwa-push')->getSchemaBuilder();

        if (!$schema->hasTable('pwa_push_subscriber')) {
            $schema->create('pwa_push_subscriber', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('auth_token', 255);
                $table->string('content_encoding', 255);
                $table->string('endpoint', 255);
                $table->string('public_key', 255);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->bigInteger('user_id')->default(0);
                $table->index('id', 'id');
                $table->index('user_id', 'user_id');
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function enableTestMode(bool $testModeEnabled = true): PwaPushInterface
    {
        $this->testModeEnabled =  $testModeEnabled;

        return $this;
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
    public function isTestModeEnabled(): bool
    {
        return $this->testModeEnabled;
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
    public function setPrivateKey(string $privateKey) : PwaPushInterface
    {
        $this->privateKey = $privateKey;

        return $this;
    }
}
