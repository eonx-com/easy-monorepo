<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhooks\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhooks\Formatters\JsonFormatter;
use EonX\EasyWebhooks\HttpClientFactory;
use EonX\EasyWebhooks\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhooks\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhooks\Interfaces\WebhookClientInterface;
use EonX\EasyWebhooks\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhooks\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhooks\Stores\NullWebhookStore;
use EonX\EasyWebhooks\WebhookClient;
use EonX\EasyWebhooks\WebhookResultHandler;
use Symfony\Component\HttpClient\HttpClient;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Body Formatter (Default)
    $services->set(WebhookBodyFormatterInterface::class, JsonFormatter::class);

    // HTTP Client
    $services
        // Factory
        ->set(HttpClientFactoryInterface::class, HttpClientFactory::class)
        // Client
        ->set(BridgeConstantsInterface::HTTP_CLIENT, HttpClient::class)
        ->factory([ref(HttpClientFactoryInterface::class), 'create']);

    // Webhook Result Handler
    $services->set(WebhookResultHandlerInterface::class, WebhookResultHandler::class);

    // Webhook Client
    $services
        ->set(WebhookClientInterface::class, WebhookClient::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_WEBHOOK_CONFIGURATOR))
        ->arg('$httpClient', ref(BridgeConstantsInterface::HTTP_CLIENT));

    // Webhook Store (Default)
    $services->set(WebhookStoreInterface::class, NullWebhookStore::class);
};
