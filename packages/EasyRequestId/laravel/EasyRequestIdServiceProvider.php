<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Laravel;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag as EasyHttpClientConfigTag;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Common\RequestId\RequestId;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\EasyErrorHandler\Builder\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\EasyHttpClient\Modifier\RequestIdRequestDataModifier;
use EonX\EasyRequestId\EasyLogging\Processor\RequestIdProcessor;
use EonX\EasyRequestId\EasyWebhook\Middleware\RequestIdWebhookMiddleware;
use EonX\EasyRequestId\Laravel\Listeners\RequestIdRouteMatchedListener;
use EonX\EasyRequestId\Laravel\Middleware\RequestIdMiddleware;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

final class EasyRequestIdServiceProvider extends ServiceProvider
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-request-id.php' => \base_path('config/easy-request-id.php'),
        ]);

        /** @var \EonX\EasyRequestId\Common\RequestId\RequestIdInterface $requestId */
        $requestId = $this->app->make(RequestIdInterface::class);

        // Queue
        // Add IDs to jobs pushed to the queue
        Queue::createPayloadUsing(static fn (): array => [
            'easy_request_id' => [
                $requestId->getCorrelationIdHeaderName() => $requestId->getCorrelationId(),
                $requestId->getRequestIdHeaderName() => $requestId->getRequestId(),
            ],
        ]);

        // Resolve IDs from jobs from the queue
        $this->app->make('events')
            ->listen(
                JobProcessing::class,
                static function (JobProcessing $event) use ($requestId): void {
                    $body = \json_decode($event->job->getRawBody(), true);

                    if (\is_array($body) === false) {
                        return;
                    }

                    $requestId->setResolver(static function () use ($body, $requestId): array {
                        $ids = $body['easy_request_id'] ?? [];

                        return [
                            RequestIdInterface::KEY_RESOLVED_CORRELATION_ID =>
                                $ids[$requestId->getCorrelationIdHeaderName()] ?? null,
                            RequestIdInterface::KEY_RESOLVED_REQUEST_ID =>
                                $ids[$requestId->getRequestIdHeaderName()] ?? null,
                        ];
                    });
                }
            );
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-request-id.php', 'easy-request-id');

        $this->app->singleton(FallbackResolverInterface::class, UuidFallbackResolver::class);

        $this->app->singleton(
            RequestIdInterface::class,
            static fn (Container $app): RequestIdInterface => new RequestId(
                $app->make(FallbackResolverInterface::class),
                \config('easy-request-id.http_headers.correlation_id'),
                \config('easy-request-id.http_headers.request_id')
            )
        );

        // Resolve from request
        $this->app->make('events')
            ->listen(RouteMatched::class, RequestIdRouteMatchedListener::class);

        if ($this->app instanceof LumenApplication) {
            $this->app->middleware([
                RequestIdMiddleware::class,
            ]);
        }

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

        // EasyHttpClient
        if ($this->bridgeEnabled('easy_http_client', EasyHttpClientConfigTag::class)) {
            $this->app->singleton(RequestIdRequestDataModifier::class);
            $this->app->tag(
                RequestIdRequestDataModifier::class,
                [EasyHttpClientConfigTag::RequestDataModifier->value]
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

    private function bridgeEnabled(string $config, string $enum): bool
    {
        $enabled = (bool)\config(\sprintf('easy-request-id.%s', $config), true);

        // @todo Remove \interface_exists after migration to new structure
        return $enabled && (\enum_exists($enum) || \interface_exists($enum));
    }
}
