<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

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
};
