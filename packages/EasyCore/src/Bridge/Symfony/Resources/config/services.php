<?php

declare(strict_types=1);

use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersSanityCheckMiddleware;
use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
