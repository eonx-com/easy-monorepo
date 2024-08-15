<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Laravel;

use Bugsnag\Client;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyHttpClient\Bundle\Enum\ConfigServiceId;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyHttpClient\EasyBugsnag\Listener\HttpRequestSentBreadcrumbListener;
use EonX\EasyHttpClient\PsrLogger\Listener\LogHttpRequestSentListener;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
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
        $this->registerEasyWebhookIntegration();

        if (\config('easy-http-client.bugsnag.enabled', true) && \class_exists(Client::class)) {
            $this->app->make('events')
                ->listen(HttpRequestSentEvent::class, HttpRequestSentBreadcrumbListener::class);
        }

        if (\config('easy-http-client.psr_logger.enabled', true) && \interface_exists(LoggerInterface::class)) {
            $this->app->make('events')
                ->listen(HttpRequestSentEvent::class, LogHttpRequestSentListener::class);
        }
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected static function instantiateClient(
        Container $app,
        ?HttpClientInterface $client = null,
    ): HttpClientInterface {
        /** @var iterable<\EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface> $modifiers */
        $modifiers = $app->tagged(ConfigTag::RequestDataModifier->value);

        return new WithEventsHttpClient(
            $app->make(EventDispatcherInterface::class),
            $client,
            $modifiers,
            \config('easy-http-client.modifiers.enabled'),
            \config('easy-http-client.modifiers.whitelist')
        );
    }

    private function registerEasyWebhookIntegration(): void
    {
        // Register only if enabled and eonx-com/easy-webhook installed
        if (\config('easy-http-client.decorate_easy_webhook_client', false) === false
            || \enum_exists(EasyWebhookConfigServiceId::class) === false) {
            return;
        }

        $this->app->extend(
            EasyWebhookConfigServiceId::HttpClient->value,
            static fn (HttpClientInterface $decorated, Container $app): HttpClientInterface => self::instantiateClient(
                $app,
                $decorated
            )
        );
    }

    private function registerHttpClient(): void
    {
        $this->app->singleton(
            ConfigServiceId::HttpClient->value,
            static fn (Container $app): HttpClientInterface => self::instantiateClient($app)
        );
    }
}
