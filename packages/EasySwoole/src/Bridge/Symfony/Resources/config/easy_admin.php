<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\EasyAdmin\AdminContextAsTwigGlobalListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AdminContextAsTwigGlobalListener::class)
        ->tag('kernel.event_listener', [
            'priority' => -100,
        ]);
};
