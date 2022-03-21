<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\Symfony\DataCollector\TraceableWebhookClient;
use EonX\EasyWebhook\Bridge\Symfony\DataCollector\WebhookDataCollector;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;

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
            'template' => '@EasyWebhookSymfony/Collector/webhook_collector.html.twig',
            'id' => WebhookDataCollector::NAME,
        ]);
};
