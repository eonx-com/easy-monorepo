<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\RetrySendWebhookMiddleware;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\SendWebhookHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AsyncDispatcher::class)
        ->set(RetrySendWebhookMiddleware::class)
        ->set(SendWebhookHandler::class);
};
