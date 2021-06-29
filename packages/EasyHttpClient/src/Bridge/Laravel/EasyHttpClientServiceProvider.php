<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyHttpClient\Bridge\EasyBugsnag\HttpRequestSentBreadcrumbListener;
use EonX\EasyHttpClient\Bridge\PsrLogger\LogHttpRequestSentListener;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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

        if (\config('easy-http-client.easy_bugsnag_enabled', true) && \class_exists(Client::class)) {
            $this->app->make('events')->listen(HttpRequestSentEvent::class, HttpRequestSentBreadcrumbListener::class);
        }

        if (\config('easy-http-client.psr_logger_enabled', true) && \interface_exists(LoggerInterface::class)) {
            $this->app->make('events')->listen(HttpRequestSentEvent::class, LogHttpRequestSentListener::class);
        }
    }
}
