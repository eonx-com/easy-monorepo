<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Common\Client\TraceableWebhookClient;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\DataCollector\WebhookDataCollector;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(TraceableWebhookClient::class)
        ->decorate(WebhookClientInterface::class, null, 3);

    $services
        ->set(WebhookDataCollector::class)
        ->arg('$webhookClient', service(TraceableWebhookClient::class))
        ->tag('data_collector', [
            'id' => 'easy_webhook',
            'template' => '@EasyWebhook/collector/webhook_collector.html.twig',
        ]);
};
