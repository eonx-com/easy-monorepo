<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Common\Listener\ShutdownStrategyListener;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->get(ShutdownStrategyListener::class)
        ->tag('kernel.event_listener', [
            'event' => EnvelopeDispatchedEvent::class,
        ]);
};
