<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyWebhook\Async\NullAsyncDispatcher;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Laravel\Commands\SendDueWebhooksCommand;
use EonX\EasyWebhook\Bridge\Laravel\Jobs\AsyncDispatcher;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\HttpClientFactory;
use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
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
use EonX\EasyWebhook\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Middleware\LockMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\RetryStrategies\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Stores\NullResultStore;
use EonX\EasyWebhook\Stores\NullStore;
use EonX\EasyWebhook\WebhookClient;
use Illuminate\Support\ServiceProvider;
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
        // BEFORE MIDDLEWARE
        $this->app->singleton(LockMiddleware::class, function (): LockMiddleware {
            return new LockMiddleware(
                $this->app->make(LockServiceInterface::class),
                MiddlewareInterface::PRIORITY_CORE_BEFORE - 1
            );
        });

        $this->app->singleton(RerunMiddleware::class, function (): RerunMiddleware {
            return new RerunMiddleware(MiddlewareInterface::PRIORITY_CORE_BEFORE);
        });

        // AFTER MIDDLEWARE
        $this->app->singleton(ResetStoreMiddleware::class, function (): ResetStoreMiddleware {
            return new ResetStoreMiddleware(
                $this->app->make(StoreInterface::class),
                $this->app->make(ResultStoreInterface::class),
                MiddlewareInterface::PRIORITY_CORE_AFTER
            );
        });

        $this->app->singleton(MethodMiddleware::class, function (): MethodMiddleware {
            return new MethodMiddleware(
                \config('easy-webhooks.method'),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 10
            );
        });

        $this->app->singleton(AsyncMiddleware::class, function (): AsyncMiddleware {
            return new AsyncMiddleware(
                $this->app->make(AsyncDispatcherInterface::class),
                $this->app->make(StoreInterface::class),
                \config('easy-webhook.send_async', true),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 20
            );
        });

        $this->app->singleton(StoreMiddleware::class, function (): StoreMiddleware {
            return new StoreMiddleware(
                $this->app->make(StoreMiddleware::class),
                $this->app->make(ResultStoreInterface::class),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 30
            );
        });

        $this->app->singleton(EventsMiddleware::class, function (): EventsMiddleware {
            return new EventsMiddleware(
                $this->app->make(EventDispatcherInterface::class),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 40
            );
        });

        $this->app->singleton(StatusAndAttemptMiddleware::class, function (): StatusAndAttemptMiddleware {
            return new StatusAndAttemptMiddleware(MiddlewareInterface::PRIORITY_CORE_AFTER + 50);
        });

        $this->app->singleton(SendWebhookMiddleware::class, function (): SendWebhookMiddleware {
            return new SendWebhookMiddleware(
                $this->app->make(BridgeConstantsInterface::HTTP_CLIENT),
                MiddlewareInterface::PRIORITY_CORE_AFTER + 60
            );
        });
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

        // HTTP Client
        $this->app->singleton(HttpClientFactoryInterface::class, HttpClientFactory::class);
        $this->app->singleton(BridgeConstantsInterface::HTTP_CLIENT, function (): HttpClientInterface {
            return $this->app->make(HttpClientFactoryInterface::class)->create();
        });

        // Stack
        $this->app->singleton(BridgeConstantsInterface::STACK, function (): StackInterface {
            return new Stack($this->app->tagged(BridgeConstantsInterface::TAG_MIDDLEWARE));
        });

        // Webhook Retry Strategy (Default)
        $this->app->singleton(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

        // Webhook Client
        $this->app->singleton(WebhookClientInterface::class, function (): WebhookClientInterface {
            return new WebhookClient($this->app->make(BridgeConstantsInterface::STACK));
        });

        // Stores (Default)
        $this->app->singleton(StoreInterface::class, NullStore::class);
        $this->app->singleton(ResultStoreInterface::class, NullResultStore::class);
    }

    private function registerEventHeaderServices(): void
    {
        if (\config('easy-webhook.event.enabled', true) === false) {
            return;
        }

        $this->app->singleton(EventHeaderMiddleware::class, function (): EventHeaderMiddleware {
            return new EventHeaderMiddleware(\config('easy-webhook.event.event_header'));
        });

        $this->app->tag(EventHeaderMiddleware::class, [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }

    private function registerIdHeaderServices(): void
    {
        if (\config('easy-webhook.id.enabled', true) === false) {
            return;
        }

        $this->app->singleton(IdHeaderMiddleware::class, function (): IdHeaderMiddleware {
            return new IdHeaderMiddleware(
                $this->app->make(StoreInterface::class),
                \config('easy-webhook.id.id_header')
            );
        });

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

        $this->app->singleton(SignatureHeaderMiddleware::class, function (): SignatureHeaderMiddleware {
            return new SignatureHeaderMiddleware(
                $this->app->make(WebhookSignerInterface::class),
                \config('easy-webhook.signature.secret'),
                \config('easy-webhook.signature.signature_header'),
                100
            );
        });

        $this->app->tag(SignatureHeaderMiddleware::class, [BridgeConstantsInterface::TAG_MIDDLEWARE]);
    }
}
