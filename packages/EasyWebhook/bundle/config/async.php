<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Messenger\Dispatcher\AsyncDispatcher;
use EonX\EasyWebhook\Messenger\MessageHandler\SendWebhookMessageHandler;
use EonX\EasyWebhook\Messenger\Middleware\RetrySendWebhookMiddleware;
use Psr\Container\ContainerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->alias(ContainerInterface::class, 'service_container');

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class)
        ->set(RetrySendWebhookMiddleware::class)
        ->set(SendWebhookMessageHandler::class);
};
