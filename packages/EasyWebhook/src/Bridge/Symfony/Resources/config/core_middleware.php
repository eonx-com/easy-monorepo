<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Middleware\EventsMiddleware;
use EonX\EasyWebhook\Middleware\LockMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Middleware\SyncRetryMiddleware;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // BEFORE MIDDLEWARE
    $services
        ->set(LockMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE - 2);

    $services
        ->set(RerunMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_BEFORE);

    // AFTER MIDDLEWARE
    $services
        ->set(SendAfterMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER);

    $services
        ->set(ResetStoreMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 1);

    $services
        ->set(MethodMiddleware::class)
        ->arg('$method', '%' . BridgeConstantsInterface::PARAM_METHOD . '%')
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 10);

    $services
        ->set(AsyncMiddleware::class)
        ->arg('$enabled', '%' . BridgeConstantsInterface::PARAM_ASYNC . '%')
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 20);

    $services
        ->set(SyncRetryMiddleware::class)
        ->arg('$asyncEnabled', '%' . BridgeConstantsInterface::PARAM_ASYNC . '%')
        ->arg('$logger', ref(LoggerInterface::class))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 30);

    $services
        ->set(StoreMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 40);

    $services
        ->set(EventsMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 50);

    $services
        ->set(StatusAndAttemptMiddleware::class)
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 60);

    // Make sure SendWebhookMiddleware is always last
    $services
        ->set(SendWebhookMiddleware::class)
        ->arg('$httpClient', ref(BridgeConstantsInterface::HTTP_CLIENT))
        ->arg('$priority', MiddlewareInterface::PRIORITY_CORE_AFTER + 70);
};
