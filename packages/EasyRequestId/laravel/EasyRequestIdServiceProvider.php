<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Laravel;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag as EasyErrorHandlerConfigTag;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag as EasyHttpClientConfigTag;
use EonX\EasyLogging\Bundle\Enum\ConfigTag as EasyLoggingConfigTag;
use EonX\EasyRequestId\Common\Provider\RequestIdProvider;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\Common\ValueObject\RequestIdInfo;
use EonX\EasyRequestId\EasyErrorHandler\Builder\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\EasyHttpClient\Modifier\RequestIdRequestDataModifier;
use EonX\EasyRequestId\EasyLogging\Processor\RequestIdProcessor;
use EonX\EasyRequestId\EasyWebhook\Middleware\RequestIdWebhookMiddleware;
use EonX\EasyRequestId\Laravel\Listeners\RequestIdRouteMatchedListener;
use EonX\EasyRequestId\Laravel\Middleware\RequestIdMiddleware;
use EonX\EasyWebhook\Bundle\Enum\ConfigTag as EasyWebhookConfigTag;
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

        /** @var \EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface $requestIdProvider */
        $requestIdProvider = $this->app->make(RequestIdProviderInterface::class);

        // Queue
        // Add IDs to jobs pushed to the queue
        Queue::createPayloadUsing(static fn (): array => [
            'easy_request_id' => [
                $requestIdProvider->getCorrelationIdHeaderName() => $requestIdProvider->getCorrelationId(),
                $requestIdProvider->getRequestIdHeaderName() => $requestIdProvider->getRequestId(),
            ],
        ]);

        // Resolve IDs from jobs from the queue
        $this->app->make('events')
            ->listen(
                JobProcessing::class,
                static function (JobProcessing $event) use ($requestIdProvider): void {
                    $body = \json_decode($event->job->getRawBody(), true);

                    if (\is_array($body) === false) {
                        return;
                    }

                    $requestIdProvider->setResolver(static function () use ($body, $requestIdProvider): RequestIdInfo {
                        $ids = $body['easy_request_id'] ?? [];

                        return new RequestIdInfo(
                            $ids[$requestIdProvider->getCorrelationIdHeaderName()] ?? null,
                            $ids[$requestIdProvider->getRequestIdHeaderName()] ?? null
                        );
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
            RequestIdProviderInterface::class,
            static fn (Container $app): RequestIdProviderInterface => new RequestIdProvider(
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
        if ($this->packageEnabled('easy_error_handler', EasyErrorHandlerConfigTag::class)) {
            $this->app->singleton(RequestIdErrorResponseBuilder::class);
            $this->app->tag(
                RequestIdErrorResponseBuilder::class,
                [EasyErrorHandlerConfigTag::ErrorResponseBuilderProvider->value]
            );
        }

        // EasyLogging
        if ($this->packageEnabled('easy_logging', EasyLoggingConfigTag::class)) {
            $this->app->singleton(RequestIdProcessor::class);
            $this->app->tag(
                RequestIdProcessor::class,
                [EasyLoggingConfigTag::ProcessorConfigProvider->value]
            );
        }

        // EasyHttpClient
        if ($this->packageEnabled('easy_http_client', EasyHttpClientConfigTag::class)) {
            $this->app->singleton(RequestIdRequestDataModifier::class);
            $this->app->tag(
                RequestIdRequestDataModifier::class,
                [EasyHttpClientConfigTag::RequestDataModifier->value]
            );
        }

        // EasyWebhook
        if ($this->packageEnabled('easy_webhook', EasyWebhookConfigTag::class)) {
            $this->app->singleton(RequestIdWebhookMiddleware::class);
            $this->app->tag(
                RequestIdWebhookMiddleware::class,
                [EasyWebhookConfigTag::Middleware->value]
            );
        }
    }

    /**
     * @param class-string<\BackedEnum> $enum
     */
    private function packageEnabled(string $config, string $enum): bool
    {
        $enabled = (bool)\config(\sprintf('easy-request-id.%s', $config), true);

        return $enabled && \enum_exists($enum);
    }
}
