<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigServiceId as EasyBugsnagConfigServiceId;
use EonX\EasyBugsnag\Common\Listener\ShutdownStrategyListener;
use EonX\EasyServerless\EasyBugsnag\Configurator\AwsLambdaConfigurator;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AwsLambdaConfigurator::class);

    $services
        ->set('easy_serverless.easy_bugsnag.shutdown_strategy_listener', ShutdownStrategyListener::class)
        ->arg('$shutdownStrategy', service(EasyBugsnagConfigServiceId::ShutdownStrategy->value))
        ->tag('kernel.event_listener', [
            'event' => EnvelopeDispatchedEvent::class,
        ]);
};
