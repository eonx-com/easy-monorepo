<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bridge\EasyBugsnag\Ignorers\ApiPlatformBugsnagExceptionIgnorer;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Providers\ApiPlatformErrorResponseBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformBugsnagExceptionIgnorer::class)
        ->arg(
            '$apiPlatformErrorResponseBuilderProvider',
            service(ApiPlatformErrorResponseBuilderProvider::class)->nullOnInvalid()
        );
};
