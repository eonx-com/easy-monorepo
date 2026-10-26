<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Datadog\Listener\PhpRuntimeInfoListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Registered via #[AsEventListener], a no-op when the ddtrace extension is not loaded
    $services->set(PhpRuntimeInfoListener::class);
};
