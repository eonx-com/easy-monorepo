<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\EasyBugsnag\HttpRequestSentBreadcrumbListener;
use EonX\EasyHttpClient\Bridge\PsrLogger\LogHttpRequestSentListener;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EasyHttpClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-http-client.php' => \base_path('config/easy-http-client.php'),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-http-client.php', 'easy-http-client');

        $this->registerHttpClient();
        $this->registerEasyWebhookBridge();

        if (\config('easy-http-client.easy_bugsnag_enabled', true) && \class_exists(Client::class)) {
            $this->app->make('events')
                ->listen(HttpRequestSentEvent::class, HttpRequestSentBreadcrumbListener::class);
        }

        if (\config('easy-http-client.psr_logger_enabled', true) && \interface_exists(LoggerInterface::class)) {
            $this->app->make('events')
                ->listen(HttpRequestSentEvent::class, LogHttpRequestSentListener::class);
        }
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected static function instantiateClient(
        Container $app,
        ?HttpClientInterface $client = null
    ): HttpClientInterface {
        /** @var iterable<\EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface> $modifiers */
        $modifiers = $app->tagged(BridgeConstantsInterface::TAG_REQUEST_DATA_MODIFIER);

        return new WithEventsHttpClient(
            $app->make(EventDispatcherInterface::class),
            $client,
            $modifiers,
            \config('easy-http-client.modifiers.enabled'),
            \config('easy-http-client.modifiers.whitelist')
        );
    }

    private function registerEasyWebhookBridge(): void
    {
        // Register only if enabled and eonx-com/easy-webhook installed.
        if (\config('easy-http-client.decorate_easy_webhook_client', false) === false
            || \interface_exists(EasyWebhookBridgeConstantsInterface::class) === false) {
            return;
        }

        $this->app->extend(
            EasyWebhookBridgeConstantsInterface::HTTP_CLIENT,
            static function (HttpClientInterface $decorated, Container $app): HttpClientInterface {
                return self::instantiateClient($app, $decorated);
            }
        );
    }

    private function registerHttpClient(): void
    {
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_HTTP_CLIENT,
            static function (Container $app): HttpClientInterface {
                return self::instantiateClient($app);
            }
        );
    }
}
