<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersSanityCheckMiddleware;
use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Messenger
    $services
        ->set(DoctrineManagersSanityCheckMiddleware::class)
        ->tag('kernel.event_listener');

    $services->set(ForBuildEnvVarProcessor::class);
};
