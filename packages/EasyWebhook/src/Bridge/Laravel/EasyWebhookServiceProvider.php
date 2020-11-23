<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Laravel\Jobs\AsyncWebhookClient;
use EonX\EasyWebhook\Configurators\BodyFormatterWebhookConfigurator;
use EonX\EasyWebhook\Configurators\EventWebhookConfigurator;
use EonX\EasyWebhook\Configurators\IdWebhookConfigurator;
use EonX\EasyWebhook\Configurators\MethodWebhookConfigurator;
use EonX\EasyWebhook\Configurators\SignatureWebhookConfigurator;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\HttpClientFactory;
use EonX\EasyWebhook\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\RetryStrategies\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stores\NullWebhookResultStore;
use EonX\EasyWebhook\WebhookClient;
use EonX\EasyWebhook\WebhookResultHandler;
use EonX\EasyWebhook\WithEventsWebhookClient;
use Illuminate\Contracts\Bus\Dispatcher;
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

        if (\config('easy-webhook.use_default_configurators', true)) {
            $this->registerDefaultConfigurators();
        }

        if (\config('easy-webhook.event.enabled', true)) {
            $this->registerEventServices();
        }

        if (\config('easy-webhook.id.enabled', true)) {
            $this->registerIdServices();
        }

        if (\config('easy-webhook.signature.enabled', false)) {
            $this->registerSignatureServices();
        }
    }

    private function registerDefaultConfigurators(): void
    {
        $this->app->singleton(BodyFormatterWebhookConfigurator::class, function (): BodyFormatterWebhookConfigurator {
            return new BodyFormatterWebhookConfigurator(
                $this->app->make(WebhookBodyFormatterInterface::class),
                BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY
            );
        });

        $this->app->singleton(MethodWebhookConfigurator::class, function (): MethodWebhookConfigurator {
            return new MethodWebhookConfigurator(
                \config('easy-webhooks.method'),
                BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY
            );
        });

        $this->app->tag(
            [BodyFormatterWebhookConfigurator::class, MethodWebhookConfigurator::class],
            [BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR]
        );
    }

    private function registerDefaultServices(): void
    {
        // Body Formatter (Default)
        $this->app->singleton(WebhookBodyFormatterInterface::class, JsonFormatter::class);

        // HTTP Client
        $this->app->singleton(HttpClientFactoryInterface::class, HttpClientFactory::class);
        $this->app->singleton(BridgeConstantsInterface::HTTP_CLIENT, function (): HttpClientInterface {
            return $this->app->make(HttpClientFactoryInterface::class)->create();
        });

        // Webhook Retry Strategy (Default)
        $this->app->singleton(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

        // Webhook Result Handler
        $this->app->singleton(WebhookResultHandlerInterface::class, WebhookResultHandler::class);

        // Webhook Client
        $this->app->singleton(WebhookClientInterface::class, function (): WebhookClientInterface {
            $client = new WebhookClient(
                $this->app->make(BridgeConstantsInterface::HTTP_CLIENT),
                $this->app->make(WebhookResultHandlerInterface::class),
                $this->app->tagged(BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR)
            );

            return new WithEventsWebhookClient($client, $this->app->make(EventDispatcherInterface::class));
        });

        // Webhook Store (Default)
        $this->app->singleton(WebhookResultStoreInterface::class, NullWebhookResultStore::class);

        if (\config('easy-webhook.send_async', true)) {
            $this->app->extend(
                WebhookClientInterface::class,
                function (WebhookClientInterface $client): WebhookClientInterface {
                    return new AsyncWebhookClient(
                        $this->app->make(Dispatcher::class),
                        $client,
                        $this->app->make(WebhookResultStoreInterface::class)
                    );
                }
            );
        }
    }

    private function registerEventServices(): void
    {
        $this->app->singleton(EventWebhookConfigurator::class, function (): EventWebhookConfigurator {
            return new EventWebhookConfigurator(\config('easy-webhook.event.event_header'));
        });

        $this->app->tag(EventWebhookConfigurator::class, [BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR]);
    }

    private function registerIdServices(): void
    {
        $this->app->singleton(IdWebhookConfigurator::class, function (): IdWebhookConfigurator {
            return new IdWebhookConfigurator(
                $this->app->make(WebhookResultStoreInterface::class),
                \config('easy-webhook.id.id_header')
            );
        });

        $this->app->tag(IdWebhookConfigurator::class, [BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR]);
    }

    private function registerSignatureServices(): void
    {
        $this->app->singleton(Rs256Signer::class);

        $this->app->singleton(SignatureWebhookConfigurator::class, function (): SignatureWebhookConfigurator {
            return new SignatureWebhookConfigurator(
                $this->app->make(\config('easy-webhook.signature.signer')),
                \config('easy-webhook.signature.secret'),
                \config('easy-webhook.signature.signature_header'),
                BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY + 1
            );
        });

        $this->app->tag(SignatureWebhookConfigurator::class, [BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR]);
    }
}
