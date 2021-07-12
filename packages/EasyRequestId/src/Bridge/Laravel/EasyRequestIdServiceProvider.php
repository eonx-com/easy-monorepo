<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\EasyErrorHandler\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\Bridge\EasyLogging\RequestIdProcessor;
use EonX\EasyRequestId\Bridge\EasyWebhook\RequestIdWebhookMiddleware;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Illuminate\Contracts\Container\Container;
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

        $this->app->singleton(
            RequestIdServiceInterface::class,
            static function (Container $app): RequestIdServiceInterface {
                return new RequestIdService(
                    $app->make(FallbackResolverInterface::class),
                    \config('easy-request-id.http_headers.correlation_id'),
                    \config('easy-request-id.http_headers.request_id')
                );
            }
        );

        // EasyErrorHandler
        if ($this->bridgeEnabled('easy_error_handler', EasyErrorHandlerBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdErrorResponseBuilder::class);
            $this->app->tag(
                RequestIdErrorResponseBuilder::class,
                [EasyErrorHandlerBridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER]
            );
        }

        // EasyLogging
        if ($this->bridgeEnabled('easy_logging', EasyLoggingBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdProcessor::class);
            $this->app->tag(
                RequestIdProcessor::class,
                [EasyLoggingBridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER]
            );
        }

        // EasyWebhook
        if ($this->bridgeEnabled('easy_webhook', EasyWebhookBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdWebhookMiddleware::class);
            $this->app->tag(
                RequestIdWebhookMiddleware::class,
                [EasyWebhookBridgeConstantsInterface::TAG_MIDDLEWARE]
            );
        }
    }

    private function bridgeEnabled(string $config, string $interface): bool
    {
        $enabled = (bool)\config(\sprintf('easy-request-id.%s', $config), true);

        return $enabled && \interface_exists($interface);
    }
}
