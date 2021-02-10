<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Async\NullAsyncDispatcher;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Symfony\Command\SendDueWebhooksCommand;
use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\HttpClientFactory;
use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\HttpClientFactoryInterface;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\RetryStrategies\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Stores\NullWebhookResultStore;
use EonX\EasyWebhook\WebhookClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Async Dispatcher (Default)
    $services->set(AsyncDispatcherInterface::class, NullAsyncDispatcher::class);

    // Body Formatter (Default)
    $services->set(WebhookBodyFormatterInterface::class, JsonFormatter::class);

    // Commands
    $services->set(SendDueWebhooksCommand::class);

    // HTTP Client
    $services
        // Factory
        ->set(HttpClientFactoryInterface::class, HttpClientFactory::class)
        // Client
        ->set(BridgeConstantsInterface::HTTP_CLIENT, HttpClientInterface::class)
        ->factory([ref(HttpClientFactoryInterface::class), 'create']);

    // Retry Strategy (Default)
    $services->set(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

    // Stack
    $services
        ->set(BridgeConstantsInterface::STACK, Stack::class)
        ->arg('$middleware', tagged_iterator(BridgeConstantsInterface::TAG_MIDDLEWARE));

    // Webhook Client
    $services
        ->set(WebhookClientInterface::class, WebhookClient::class)
        ->arg('$stack', ref(BridgeConstantsInterface::STACK));

    // Webhook Store (Default)
    $services->set(WebhookResultStoreInterface::class, NullWebhookResultStore::class);
};
