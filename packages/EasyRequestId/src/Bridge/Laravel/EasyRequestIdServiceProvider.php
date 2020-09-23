<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\EasyBugsnag\RequestIdConfigurator;
use EonX\EasyRequestId\Bridge\EasyErrorHandler\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\Bridge\EasyLogging\RequestIdProcessor;
use EonX\EasyRequestId\Bridge\EasyWebhook\RequestIdWebhookConfigurator;
use EonX\EasyRequestId\DefaultResolver;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Illuminate\Support\ServiceProvider;

final class EasyRequestIdServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-request-id.php' => \base_path('config/easy-request-id.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-request-id.php', 'easy-request-id');

        $this->app->singleton(FallbackResolverInterface::class, UuidV4FallbackResolver::class);

        $this->app->singleton(RequestIdServiceInterface::class, function (): RequestIdServiceInterface {
            return new RequestIdService(
                $this->app->tagged(BridgeConstantsInterface::TAG_REQUEST_ID_RESOLVER),
                $this->app->tagged(BridgeConstantsInterface::TAG_CORRELATION_ID_RESOLVER),
                $this->app->make(FallbackResolverInterface::class)
            );
        });

        if ((bool)\config('easy-request-id.default_resolver', true)) {
            $this->app->singleton(DefaultResolver::class, function (): DefaultResolver {
                return new DefaultResolver(
                    \config('easy-request-id.default_request_id_header'),
                    \config('easy-request-id.default_correlation_id_header')
                );
            });
        }

        // EasyBugsnag
        if ($this->bridgeEnabled('easy_bugsnag', EasyBugsnagBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdConfigurator::class);
            $this->app->extend(RequestIdConfigurator::class, $this->getSetKeysClosure());
            $this->app->tag(
                RequestIdConfigurator::class,
                [EasyBugsnagBridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]
            );
        }

        // EasyErrorHandler
        if ($this->bridgeEnabled('easy_error_handler', EasyErrorHandlerBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdErrorResponseBuilder::class);
            $this->app->extend(RequestIdErrorResponseBuilder::class, $this->getSetKeysClosure());
            $this->app->tag(
                RequestIdErrorResponseBuilder::class,
                [EasyErrorHandlerBridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER]
            );
        }

        // EasyLogging
        if ($this->bridgeEnabled('easy_logging', EasyLoggingBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdProcessor::class);
            $this->app->extend(RequestIdProcessor::class, $this->getSetKeysClosure());
            $this->app->tag(
                RequestIdProcessor::class,
                [EasyLoggingBridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER]
            );
        }

        // EasyWebhook
        if ($this->bridgeEnabled('easy_webhook', EasyWebhookBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdWebhookConfigurator::class);
            $this->app->extend(RequestIdWebhookConfigurator::class, $this->getSetKeysClosure());
            $this->app->tag(
                RequestIdWebhookConfigurator::class,
                [EasyWebhookBridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR]
            );
        }
    }

    private function bridgeEnabled(string $config, string $interface): bool
    {
        $enabled = (bool)\config(\sprintf('easy-request-id.%s', $config), true);

        return $enabled && \interface_exists($interface);
    }

    private function getSetKeysClosure(): \Closure
    {
        return static function (RequestIdKeysAwareInterface $requestIdKeysAware): RequestIdKeysAwareInterface {
            $requestIdKeysAware->setCorrelationIdKey(\config('easy-request-id.correlation_id_key'));
            $requestIdKeysAware->setRequestIdKey(\config('easy-request-id.request_id_key'));

            return $requestIdKeysAware;
        };
    }
}
