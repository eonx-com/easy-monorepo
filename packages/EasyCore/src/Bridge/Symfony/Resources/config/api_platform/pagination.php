<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\CustomPaginationListener;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CustomPaginationListener::class)
        ->tag('kernel.event_listener', [
            'event' => 'kernel.view',
            'priority' => 17,
        ]);

    $services->set(SerializerContextBuilder::class)
        ->autoconfigure(false)
        ->decorate('api_platform.serializer.context_builder')
        ->args([service('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Pagination\SerializerContextBuilder.inner')]);
};
