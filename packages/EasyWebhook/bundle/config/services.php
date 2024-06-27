<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Bundle\Enum\ConfigTag;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Cleaner\NullDataCleaner;
use EonX\EasyWebhook\Common\Client\WebhookClient;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Command\SendDueWebhooksCommand;
use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Dispatcher\NullAsyncDispatcher;
use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\Factory\HttpClientFactoryInterface;
use EonX\EasyWebhook\Common\Formatter\JsonWebhookBodyFormatter;
use EonX\EasyWebhook\Common\Formatter\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Common\Store\NullResultStore;
use EonX\EasyWebhook\Common\Store\NullStore;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Common\Strategy\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Common\Strategy\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Doctrine\Provider\DoctrineDbalStatementProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Async Dispatcher (Default)
    $services->set(AsyncDispatcherInterface::class, NullAsyncDispatcher::class);

    // Body Formatter (Default)
    $services->set(WebhookBodyFormatterInterface::class, JsonWebhookBodyFormatter::class);

    // Commands
    $services->set(SendDueWebhooksCommand::class);

    // Data Cleaner (Default)
    $services->set(DataCleanerInterface::class, NullDataCleaner::class);

    // HTTP Client
    $services
        // Factory
        ->set(HttpClientFactoryInterface::class, HttpClientFactory::class)
        // Client
        ->set(ConfigServiceId::HttpClient->value, HttpClientInterface::class)
        ->factory([service(HttpClientFactoryInterface::class), 'create']);

    // Retry Strategy (Default)
    $services->set(WebhookRetryStrategyInterface::class, MultiplierWebhookRetryStrategy::class);

    // Stack
    $services
        ->set(ConfigServiceId::Stack->value, Stack::class)
        ->arg('$middleware', tagged_iterator(ConfigTag::Middleware->value));

    // Webhook Client
    $services
        ->set(WebhookClientInterface::class, WebhookClient::class)
        ->arg('$stack', service(ConfigServiceId::Stack->value));

    // Stores (Default)
    $services->set(StoreInterface::class, NullStore::class);
    $services->set(ResultStoreInterface::class, NullResultStore::class);

    // StatementsProvider (Helper)
    $services
        ->set(DoctrineDbalStatementProvider::class)
        ->public();
};
