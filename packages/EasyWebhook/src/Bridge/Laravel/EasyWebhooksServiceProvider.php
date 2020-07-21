<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Configurators\BodyFormatterWebhookConfigurator;
use EonX\EasyWebhook\Configurators\MethodWebhookConfigurator;
use EonX\EasyWebhook\Configurators\SignatureWebhookConfigurator;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\HttpClientFactory;
use EonX\EasyWebhook\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhook\RetryStrategies\NullWebhookRetryStrategy;
use EonX\EasyWebhook\Signers\Rs256Signer;
use EonX\EasyWebhook\Stores\NullWebhookStore;
use EonX\EasyWebhook\WebhookClient;
use EonX\EasyWebhook\WebhookResultHandler;
use Illuminate\Support\ServiceProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EasyWebhooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-webhooks.php' => \base_path('config/easy-webhooks.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-webhooks.php', 'easy-webhooks');

        $this->registerDefaultServices();

        if (\config('easy-webhooks.use_default_configurators', true)) {
            $this->registerDefaultConfigurators();
        }

        if (\config('easy-webhooks.signature.enabled', false)) {
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
        $this->app->singleton(WebhookRetryStrategyInterface::class, NullWebhookRetryStrategy::class);

        // Webhook Result Handler
        $this->app->singleton(WebhookResultHandlerInterface::class, WebhookResultHandler::class);

        // Webhook Client
        $this->app->singleton(WebhookClientInterface::class, function (): WebhookClientInterface {
            return new WebhookClient(
                $this->app->make(BridgeConstantsInterface::HTTP_CLIENT),
                $this->app->make(WebhookResultHandlerInterface::class),
                $this->app->tagged(BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR)
            );
        });

        // Webhook Store (Default)
        $this->app->singleton(WebhookStoreInterface::class, NullWebhookStore::class);
    }

    private function registerSignatureServices(): void
    {
        $this->app->singleton(Rs256Signer::class);

        $this->app->singleton(SignatureWebhookConfigurator::class, function (): SignatureWebhookConfigurator {
            return new SignatureWebhookConfigurator(
                $this->app->make(\config('easy-webhooks.signature.signer')),
                \config('easy-webhooks.signature.secret'),
                \config('easy-webhooks.signature.signature_header'),
                BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY + 1
            );
        });
    }
}
