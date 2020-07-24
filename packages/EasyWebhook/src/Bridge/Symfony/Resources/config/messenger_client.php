<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\Symfony\Messenger\AsyncWebhookClient;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\RetrySendWebhookMiddleware;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\SendWebhookHandler;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RetrySendWebhookMiddleware::class)
        ->set(SendWebhookHandler::class);

    $services
        ->set(AsyncWebhookClient::class)
        ->decorate(WebhookClientInterface::class, null, 2)
        ->arg('$client', ref(\sprintf('%s.inner', AsyncWebhookClient::class)));
};
