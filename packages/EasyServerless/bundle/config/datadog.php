<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Datadog\Listener\PhpRuntimeInfoListener;
use EonX\EasyServerless\Datadog\Tracer\EnvelopeTracer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Registered via #[AsEventListener]
    $services->set(PhpRuntimeInfoListener::class);

    // Used by the SqsHandler, when the BrefMessengerBundle is enabled
    $services->set(EnvelopeTracer::class);
};
