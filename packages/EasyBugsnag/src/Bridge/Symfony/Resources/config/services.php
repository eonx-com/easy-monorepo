<?php

declare(strict_types=1);

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Symfony\Request\SymfonyRequestResolver;
use EonX\EasyBugsnag\Bridge\Symfony\Shutdown\ShutdownStrategyListener;
use EonX\EasyBugsnag\ClientFactory;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Configurators
    $services
        ->set(BasicsConfigurator::class)
        ->arg('$projectRoot', '%kernel.project_dir%/src')
        ->arg('$stripPath', '%kernel.project_dir%')
        ->arg('$releaseStage', '%env(APP_ENV)%');

    $services
        ->set(RuntimeVersionConfigurator::class)
        ->arg('$runtime', 'symfony')
        ->arg('$version', Kernel::VERSION);

    // Client Factory + Client
    $services
        ->set(ClientFactoryInterface::class, ClientFactory::class)
        ->call('setConfigurators', [tagged_iterator(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR)])
        ->call('setRequestResolver', [ref(SymfonyRequestResolver::class)])
        ->call('setShutdownStrategy', [ref(ShutdownStrategyListener::class)]);

    $services
        ->set(Client::class)
        ->factory([ref(ClientFactoryInterface::class), 'create'])
        ->args(['%' . BridgeConstantsInterface::PARAM_API_KEY . '%']);

    // Request Resolver
    $services->set(SymfonyRequestResolver::class);

    // Shutdown Strategy
    $services
        ->set(ShutdownStrategyListener::class)
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
