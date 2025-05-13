<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Common\Factory\PaginationSchemaFactory;
use EonX\EasyApiPlatform\Common\Listener\HttpKernelViewListener;
use EonX\EasyApiPlatform\Common\SerializerContextBuilder\SerializerContextBuilder;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(HttpKernelViewListener::class)
        ->tag('kernel.event_listener', [
            'event' => 'kernel.view',
            'priority' => 17,
        ]);

    $services->set(SerializerContextBuilder::class)
        ->autoconfigure(false)
        ->decorate('api_platform.serializer.context_builder');

    $services->set(PaginationSchemaFactory::class)
        ->arg('1', service('api_platform.pagination_options'))
        ->decorate('api_platform.json_schema.schema_factory');
};
