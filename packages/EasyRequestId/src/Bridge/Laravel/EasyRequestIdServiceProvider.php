<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyErrorHandler\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface as EasyHttpClientBridgeConstantsInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\EasyErrorHandler\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\Bridge\EasyHttpClient\RequestIdRequestDataModifier;
use EonX\EasyRequestId\Bridge\EasyLogging\RequestIdProcessor;
use EonX\EasyRequestId\Bridge\EasyWebhook\RequestIdWebhookMiddleware;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidFallbackResolver;
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

        /** @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface $requestIdService */
        $requestIdService = $this->app->make(RequestIdServiceInterface::class);

        // Queue
        // Add IDs to jobs pushed to the queue
        Queue::createPayloadUsing(static fn (): array => [
            'easy_request_id' => [
                $requestIdService->getCorrelationIdHeaderName() => $requestIdService->getCorrelationId(),
                $requestIdService->getRequestIdHeaderName() => $requestIdService->getRequestId(),
            ],
        ]);

        // Resolve IDs from jobs from the queue
        $this->app->make('events')
            ->listen(
                JobProcessing::class,
                static function (JobProcessing $event) use ($requestIdService): void {
                    $body = \json_decode($event->job->getRawBody(), true);

                    if (\is_array($body) === false) {
                        return;
                    }

                    $requestIdService->setResolver(static function () use ($body, $requestIdService): array {
                        $ids = $body['easy_request_id'] ?? [];

                        return [
                            RequestIdServiceInterface::KEY_RESOLVED_CORRELATION_ID =>
                                $ids[$requestIdService->getCorrelationIdHeaderName()] ?? null,
                            RequestIdServiceInterface::KEY_RESOLVED_REQUEST_ID =>
                                $ids[$requestIdService->getRequestIdHeaderName()] ?? null,
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
            RequestIdServiceInterface::class,
            static fn (Container $app): RequestIdServiceInterface => new RequestIdService(
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
        if ($this->bridgeEnabled('easy_http_client', EasyHttpClientBridgeConstantsInterface::class)) {
            $this->app->singleton(RequestIdRequestDataModifier::class);
            $this->app->tag(
                RequestIdRequestDataModifier::class,
                [EasyHttpClientBridgeConstantsInterface::TAG_REQUEST_DATA_MODIFIER]
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
