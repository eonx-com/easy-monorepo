<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginationListener;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CustomPaginationListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.view', 'priority' => 17]);

    $services->set(SerializerContextBuilder::class)
        ->autoconfigure(false)
        ->decorate('api_platform.serializer.context_builder')
        ->args([ref('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder.inner')]);
};
