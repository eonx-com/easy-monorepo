<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Middleware\HandleExceptionsMiddleware;
use EonX\EasyWebhook\Middleware\LockMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Middleware\SyncRetryMiddleware;

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
        ->arg('$method', '%' . BridgeConstantsInterface::PARAM_METHOD . '%')
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER);

    $services
        ->set(SendAfterMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 1);

    $services
        ->set(AsyncMiddleware::class)
        ->arg('$enabled', '%' . BridgeConstantsInterface::PARAM_ASYNC . '%')
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 2);

    $services
        ->set(SyncRetryMiddleware::class)
        ->arg('$asyncEnabled', '%' . BridgeConstantsInterface::PARAM_ASYNC . '%')
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 3)
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);

    // Make sure SendWebhookMiddleware is always last
    $services
        ->set(SendWebhookMiddleware::class)
        ->arg('$httpClient', service(BridgeConstantsInterface::HTTP_CLIENT))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 4);
};
