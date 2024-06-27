<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Bundle\Enum\ConfigServiceId;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag;
use EonX\EasyBugsnag\Factory\ClientFactory;
use EonX\EasyBugsnag\Factory\ClientFactoryInterface;
use EonX\EasyBugsnag\Listener\ShutdownStrategyListener;
use EonX\EasyBugsnag\Resolver\SymfonyRequestResolver;
use EonX\EasyBugsnag\Strategy\ShutdownStrategy;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Client Factory + Client
    $services
        ->set(ClientFactoryInterface::class, ClientFactory::class)
        ->call('setConfigurators', [tagged_iterator(ConfigTag::ClientConfigurator->value)])
        ->call('setRequestResolver', [service(ConfigServiceId::RequestResolver->value)])
        ->call('setShutdownStrategy', [service(ConfigServiceId::ShutdownStrategy->value)]);

    $services
        ->set(Client::class)
        ->factory([service(ClientFactoryInterface::class), 'create'])
        ->args([param(ConfigParam::ApiKey->value)]);

    // Request Resolver
    $services->set(ConfigServiceId::RequestResolver->value, SymfonyRequestResolver::class);

    // Shutdown Strategy
    $services->set(ConfigServiceId::ShutdownStrategy->value, ShutdownStrategy::class);

    $services
        ->set(ShutdownStrategyListener::class)
        ->arg('$shutdownStrategy', service(ConfigServiceId::ShutdownStrategy->value))
        ->tag('kernel.event_listener', [
            'event' => TerminateEvent::class,
        ])
        ->tag('kernel.event_listener', [
            'event' => ConsoleTerminateEvent::class,
        ])
        ->tag('kernel.event_listener', [
            'event' => WorkerRunningEvent::class,
        ]);
};
