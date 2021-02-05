<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\RetrySendWebhookMiddleware;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\SendWebhookHandler;
use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class)
        ->set(RetrySendWebhookMiddleware::class)
        ->set(SendWebhookHandler::class);
};
