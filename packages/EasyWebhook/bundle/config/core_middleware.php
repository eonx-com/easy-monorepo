<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bundle\Enum\BundleParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Common\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Common\Middleware\HandleExceptionsMiddleware;
use EonX\EasyWebhook\Common\Middleware\LockMiddleware;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Middleware\MiddlewareInterface;
use EonX\EasyWebhook\Common\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Common\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Common\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Common\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Common\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Common\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Common\Middleware\SyncRetryMiddleware;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // BEFORE MIDDLEWARE
    $services
        ->set(LockMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 6);

    $services
        ->set(StoreMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 5);

    $services
        ->set(EventsMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 4);

    $services
        ->set(StatusAndAttemptMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 3);

    $services
        ->set(HandleExceptionsMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 2);

    $services
        ->set(ResetStoreMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 1);

    $services
        ->set(RerunMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE);

    // AFTER MIDDLEWARE
    $services
        ->set(MethodMiddleware::class)
        ->arg('$method', param(ConfigParam::Method->value))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER);

    $services
        ->set(SendAfterMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 1);

    $services
        ->set(AsyncMiddleware::class)
        ->arg('$enabled', param(ConfigParam::Async->value))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 2);

    $services
        ->set(SyncRetryMiddleware::class)
        ->arg('$asyncEnabled', param(ConfigParam::Async->value))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 3)
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    // Make sure SendWebhookMiddleware is always last
    $services
        ->set(SendWebhookMiddleware::class)
        ->arg('$httpClient', service(ConfigServiceId::HttpClient->value))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 4);
};
