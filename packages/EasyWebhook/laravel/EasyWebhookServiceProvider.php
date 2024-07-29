<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Laravel;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLogging\Bundle\Enum\BundleParam as EasyLoggingBundleParam;
use EonX\EasyWebhook\Bundle\Enum\BundleParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Bundle\Enum\ConfigTag;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Cleaner\NullDataCleaner;
use EonX\EasyWebhook\Common\Client\WebhookClient;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Dispatcher\NullAsyncDispatcher;
use EonX\EasyWebhook\Common\Enum\MiddlewarePriority;
use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\Factory\HttpClientFactoryInterface;
use EonX\EasyWebhook\Common\Formatter\JsonWebhookBodyFormatter;
use EonX\EasyWebhook\Common\Formatter\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Common\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Common\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Common\Middleware\EventHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Common\Middleware\HandleExceptionsMiddleware;
use EonX\EasyWebhook\Common\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\LockMiddleware;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Common\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Common\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Common\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Common\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Common\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Common\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Common\Middleware\SyncRetryMiddleware;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use EonX\EasyWebhook\Common\Signer\WebhookSignerInterface;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\NullResultStore;
use EonX\EasyWebhook\Common\Store\NullStore;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Common\Strategy\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Common\Strategy\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Doctrine\Provider\DoctrineDbalStatementProvider;
use EonX\EasyWebhook\Laravel\Commands\SendDueWebhooksCommand;
use EonX\EasyWebhook\Laravel\Dispatchers\AsyncDispatcher;
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
                $app->make(LockerInterface::class),
                null,
                MiddlewarePriority::CoreBefore->value - 6
            ),
            StoreMiddleware::class => static fn (Container $app): StoreMiddleware => new StoreMiddleware(
                $app->make(StoreInterface::class),
                $app->make(ResultStoreInterface::class),
                MiddlewarePriority::CoreBefore->value - 5
            ),
            EventsMiddleware::class => static fn (Container $app): EventsMiddleware => new EventsMiddleware(
                $app->make(EventDispatcherInterface::class),
                MiddlewarePriority::CoreBefore->value - 4
            ),
            StatusAndAttemptMiddleware::class => static fn (
            ): StatusAndAttemptMiddleware => new StatusAndAttemptMiddleware(
                MiddlewarePriority::CoreBefore->value - 3
            ),
            HandleExceptionsMiddleware::class => static fn (
            ): HandleExceptionsMiddleware => new HandleExceptionsMiddleware(
                MiddlewarePriority::CoreBefore->value - 2
            ),
            ResetStoreMiddleware::class => static fn (Container $app): ResetStoreMiddleware => new ResetStoreMiddleware(
                $app->make(StoreInterface::class),
                $app->make(ResultStoreInterface::class),
                MiddlewarePriority::CoreBefore->value - 1
            ),
            RerunMiddleware::class => static fn (): RerunMiddleware => new RerunMiddleware(
                MiddlewarePriority::CoreBefore->value
            ),
            // AFTER MIDDLEWARE
            MethodMiddleware::class => static fn (): MethodMiddleware => new MethodMiddleware(
                \config('easy-webhook.method'),
                MiddlewarePriority::CoreAfter->value
            ),
            SendAfterMiddleware::class => static fn (Container $app): SendAfterMiddleware => new SendAfterMiddleware(
                $app->make(StoreInterface::class),
                MiddlewarePriority::CoreAfter->value + 1
            ),
            AsyncMiddleware::class => static fn (Container $app): AsyncMiddleware => new AsyncMiddleware(
                $app->make(AsyncDispatcherInterface::class),
                $app->make(StoreInterface::class),
                \config('easy-webhook.send_async', true),
                MiddlewarePriority::CoreAfter->value + 2
            ),
            SyncRetryMiddleware::class => static function (Container $app): SyncRetryMiddleware {
                $loggerParams = \enum_exists(EasyLoggingBundleParam::class)
                    ? [EasyLoggingBundleParam::KeyChannel->value => BundleParam::LogChannel]
                    : [];

                return new SyncRetryMiddleware(
                    $app->make(ResultStoreInterface::class),
                    $app->make(WebhookRetryStrategyInterface::class),
                    \config('easy-webhook.send_async', true),
                    $app->make(LoggerInterface::class, $loggerParams),
                    MiddlewarePriority::CoreAfter->value + 3
                );
            },
            SendWebhookMiddleware::class => static fn (
                Container $app,
            ): SendWebhookMiddleware => new SendWebhookMiddleware(
                $app->make(ConfigServiceId::HttpClient->value),
                MiddlewarePriority::CoreAfter->value + 4
            ),
        ];

        foreach ($coreMiddlewareList as $class => $closure) {
            $this->app->singleton($class, $closure);
            $this->app->tag([$class], [ConfigTag::Middleware->value]);
        }
    }

    private function registerDefaultMiddleware(): void
    {
        if (\config('easy-webhook.use_default_middleware', true) === false) {
            return;
        }

        $this->app->singleton(BodyFormatterMiddleware::class);

        $this->app->tag([BodyFormatterMiddleware::class], [ConfigTag::Middleware->value]);
    }

    private function registerDefaultServices(): void
    {
        // Async Dispatcher (Default)
        $this->app->singleton(AsyncDispatcherInterface::class, NullAsyncDispatcher::class);

        // Body Formatter (Default)
        $this->app->singleton(WebhookBodyFormatterInterface::class, JsonWebhookBodyFormatter::class);

        // Data Cleaner (Default)
        $this->app->singleton(DataCleanerInterface::class, NullDataCleaner::class);

        // HTTP Client
        $this->app->singleton(HttpClientFactoryInterface::class, HttpClientFactory::class);
        $this->app->singleton(
            ConfigServiceId::HttpClient->value,
            static fn (Container $app): HttpClientInterface => $app->make(HttpClientFactoryInterface::class)->create()
        );

        // Stack
        $this->app->singleton(
            ConfigServiceId::Stack->value,
            static fn (Container $app): StackInterface => new Stack(
                $app->tagged(ConfigTag::Middleware->value)
            )
        );

        // Webhook Retry Strategy (Default)
        $this->app->singleton(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

        // Webhook Client
        $this->app->singleton(
            WebhookClientInterface::class,
            static fn (Container $app): WebhookClientInterface => new WebhookClient(
                $app->make(ConfigServiceId::Stack->value)
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

        $this->app->tag(EventHeaderMiddleware::class, [ConfigTag::Middleware->value]);
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

        $this->app->tag(IdHeaderMiddleware::class, [ConfigTag::Middleware->value]);
    }

    private function registerSignatureServices(): void
    {
        if (\config('easy-webhook.signature.enabled', false) === false) {
            return;
        }

        $this->app->singleton(
            WebhookSignerInterface::class,
            \config('easy-webhook.signature.signer', Rs256WebhookSigner::class)
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

        $this->app->tag(SignatureHeaderMiddleware::class, [ConfigTag::Middleware->value]);
    }

    private function registerStatementsProvider(): void
    {
        $this->app->singleton(DoctrineDbalStatementProvider::class);
    }
}
