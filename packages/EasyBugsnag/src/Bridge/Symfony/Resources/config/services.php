<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Symfony\Request\SymfonyRequestResolver;
use EonX\EasyBugsnag\Bridge\Symfony\Shutdown\ShutdownStrategyListener;
use EonX\EasyBugsnag\ClientFactory;
use EonX\EasyBugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use EonX\EasyBugsnag\Shutdown\ShutdownStrategy;
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
        ->call('setConfigurators', [tagged_iterator(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR)])
        ->call('setRequestResolver', [service(BridgeConstantsInterface::SERVICE_REQUEST_RESOLVER)])
        ->call('setShutdownStrategy', [service(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY)]);

    $services
        ->set(Client::class)
        ->factory([service(ClientFactoryInterface::class), 'create'])
        ->args(['%' . BridgeConstantsInterface::PARAM_API_KEY . '%']);

    // Request Resolver
    $services->set(BridgeConstantsInterface::SERVICE_REQUEST_RESOLVER, SymfonyRequestResolver::class);

    // Shutdown Strategy
    $services->set(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY, ShutdownStrategy::class);

    $services
        ->set(ShutdownStrategyListener::class)
        ->arg('$shutdownStrategy', service(BridgeConstantsInterface::SERVICE_SHUTDOWN_STRATEGY))
        ->tag('kernel.event_listener', [
            'event' => TerminateEvent::class,
        ])
        ->tag('kernel.event_listener', [
            'event' => ConsoleTerminateEvent::class,
        ])
        ->tag('kernel.event_listener', [
            'event' => WorkerRunningEvent::class,
        ]);

    $services->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', param(BridgeConstantsInterface::PARAM_HANDLED_EXCEPTIONS));
};
