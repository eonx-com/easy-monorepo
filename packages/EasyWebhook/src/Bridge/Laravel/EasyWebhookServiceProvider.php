<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstants;
use EonX\EasyWebhook\Async\NullAsyncDispatcher;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyWebhook\Bridge\Laravel\Commands\SendDueWebhooksCommand;
use EonX\EasyWebhook\Bridge\Laravel\Jobs\AsyncDispatcher;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\HttpClientFactory;
use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Interfaces\WebhookSignerInterface;
use EonX\EasyWebhook\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Middleware\EventHeaderMiddleware;
use EonX\EasyWebhook\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Middleware\HandleExceptionsMiddleware;
use EonX\EasyWebhook\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Middleware\LockMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Middleware\SyncRetryMiddleware;
use EonX\EasyWebhook\RetryStrategies\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Stores\NullDataCleaner;
use EonX\EasyWebhook\Stores\NullResultStore;
use EonX\EasyWebhook\Stores\NullStore;
use EonX\EasyWebhook\WebhookClient;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EasyWebhookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-webhook.php' => \base_path('config/easy-webhook.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-webhook.php', 'easy-webhook');

        $this->registerDefaultServices();

        $this->registerAsyncServices();
        $this->registerCommands();
        $this->registerCoreMiddleware();
        $this->registerDefaultMiddleware();
        $this->registerEventHeaderServices();
        $this->registerIdHeaderServices();
        $this->registerSignatureServices();
        $this->registerStatementsProvider();
    }

    private function registerAsyncServices(): void
    {
        if (\config('easy-webhook.send_async', true) === false) {
            return;
        }

        $this->app->singleton(AsyncDispatcherInterface::class, AsyncDispatcher::class);
    }

    private function registerCommands(): void
    {
        $this->commands([SendDueWebhooksCommand::class]);
    }

    private function registerCoreMiddleware(): void
    {
        // Middleware::class => Closure
        $coreMiddlewareList = [
            // BEFORE MIDDLEWARE
            LockMiddleware::class => static fn (Container $app): LockMiddleware => new LockMiddleware(
                $app->make(LockServiceInterface::class),
                null,
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 6
            ),
            StoreMiddleware::class => static fn (Container $app): StoreMiddleware => new StoreMiddleware(
                $app->make(StoreInterface::class),
                $app->make(ResultStoreInterface::class),
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 5
            ),
            EventsMiddleware::class => static fn (Container $app): EventsMiddleware => new EventsMiddleware(
                $app->make(EventDispatcherInterface::class),
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 4
            ),
            StatusAndAttemptMiddleware::class => static fn (
            ): StatusAndAttemptMiddleware => new StatusAndAttemptMiddleware(
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 3
            ),
            HandleExceptionsMiddleware::class => static fn (
            ): HandleExceptionsMiddleware => new HandleExceptionsMiddleware(
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 2
            ),
            ResetStoreMiddleware::class => static fn (Container $app): ResetStoreMiddleware => new ResetStoreMiddleware(
                $app->make(StoreInterface::class),
                $app->make(ResultStoreInterface::class),
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 1
            ),
            RerunMiddleware::class => static fn (): RerunMiddleware => new RerunMiddleware(
                MiddlewareInterface::PRIORITY_CORE_BEFORE
            ),
            // AFTER MIDDLEWARE
            MethodMiddleware::class => static fn (): MethodMiddleware => new MethodMiddleware(
                \config('easy-webhook.method'),
                MiddlewareInterface::PRIORITY_CORE_AFTER
            ),
            SendAfterMiddleware::class => static fn (Container $app): SendAfterMiddleware => new SendAfterMiddleware(
                $app->make(StoreInterface::class),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 1
            ),
            AsyncMiddleware::class => static fn (Container $app): AsyncMiddleware => new AsyncMiddleware(
                $app->make(AsyncDispatcherInterface::class),
                $app->make(StoreInterface::class),
                \config('easy-webhook.send_async', true),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 2
            ),
            SyncRetryMiddleware::class => static function (Container $app): SyncRetryMiddleware {
                $loggerParams = \interface_exists(EasyLoggingBridgeConstants::class)
                    ? [EasyLoggingBridgeConstants::KEY_CHANNEL => BridgeConstantsInterface::LOG_CHANNEL]
                    : [];

                return new SyncRetryMiddleware(
                    $app->make(ResultStoreInterface::class),
                    $app->make(WebhookRetryStrategyInterface::class),
                    \config('easy-webhook.send_async', true),
                    $app->make(LoggerInterface::class, $loggerParams),
                    MiddlewareInterface::PRIORITY_CORE_AFTER + 3
                );
            },
            SendWebhookMiddleware::class => static fn (
                Container $app,
            ): SendWebhookMiddleware => new SendWebhookMiddleware(
                $app->make(BridgeConstantsInterface::HTTP_CLIENT),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 4
            ),
        ];

        foreach ($coreMiddlewareList as $class => $closure) {
            $this->app->singleton($class, $closure);
            $this->app->tag([$class], [BridgeConstantsInterface::TAG_MIDDLEWARE]);
        }
    }

    private function registerDefaultMiddleware(): void
    {
        if (\config('easy-webhook.use_default_middleware', true) === false) {
            return;
        }

        $this->app->singleton(BodyFormatterMiddleware::class);

        $this->app->tag([BodyFormatterMiddleware::class], [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }

    private function registerDefaultServices(): void
    {
        // Async Dispatcher (Default)
        $this->app->singleton(AsyncDispatcherInterface::class, NullAsyncDispatcher::class);

        // Body Formatter (Default)
        $this->app->singleton(WebhookBodyFormatterInterface::class, JsonFormatter::class);

        // Data Cleaner (Default)
        $this->app->singleton(DataCleanerInterface::class, NullDataCleaner::class);

        // HTTP Client
        $this->app->singleton(HttpClientFactoryInterface::class, HttpClientFactory::class);
        $this->app->singleton(
            BridgeConstantsInterface::HTTP_CLIENT,
            static fn (Container $app): HttpClientInterface => $app->make(HttpClientFactoryInterface::class)->create()
        );

        // Stack
        $this->app->singleton(
            BridgeConstantsInterface::STACK,
            static fn (Container $app): StackInterface => new Stack(
                $app->tagged(BridgeConstantsInterface::TAG_MIDDLEWARE)
            )
        );

        // Webhook Retry Strategy (Default)
        $this->app->singleton(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

        // Webhook Client
        $this->app->singleton(
            WebhookClientInterface::class,
            static fn (Container $app): WebhookClientInterface => new WebhookClient(
                $app->make(BridgeConstantsInterface::STACK)
            )
        );

        // Stores (Default)
        $this->app->singleton(StoreInterface::class, NullStore::class);
        $this->app->singleton(ResultStoreInterface::class, NullResultStore::class);
    }

    private function registerEventHeaderServices(): void
    {
        if (\config('easy-webhook.event.enabled', true) === false) {
            return;
        }

        $this->app->singleton(
            EventHeaderMiddleware::class,
            static fn (): EventHeaderMiddleware => new EventHeaderMiddleware(
                \config('easy-webhook.event.event_header')
            )
        );

        $this->app->tag(EventHeaderMiddleware::class, [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }

    private function registerIdHeaderServices(): void
    {
        if (\config('easy-webhook.id.enabled', true) === false) {
            return;
        }

        $this->app->singleton(
            IdHeaderMiddleware::class,
            static fn (Container $app): IdHeaderMiddleware => new IdHeaderMiddleware(
                $app->make(StoreInterface::class),
                \config('easy-webhook.id.id_header')
            )
        );

        $this->app->tag(IdHeaderMiddleware::class, [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }

    private function registerSignatureServices(): void
    {
        if (\config('easy-webhook.signature.enabled', false) === false) {
            return;
        }

        $this->app->singleton(
            WebhookSignerInterface::class,
            \config('easy-webhook.signature.signer', Rs256Signer::class)
        );

        $this->app->singleton(
            SignatureHeaderMiddleware::class,
            static fn (Container $app): SignatureHeaderMiddleware => new SignatureHeaderMiddleware(
                $app->make(WebhookSignerInterface::class),
                \config('easy-webhook.signature.secret'),
                \config('easy-webhook.signature.signature_header'),
                100
            )
        );

        $this->app->tag(SignatureHeaderMiddleware::class, [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }

    private function registerStatementsProvider(): void
    {
        $this->app->singleton(DbalStatementsProvider::class);
    }
}
