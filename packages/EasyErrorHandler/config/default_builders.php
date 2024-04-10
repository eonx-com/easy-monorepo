<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Config\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Providers\DefaultErrorResponseBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultErrorResponseBuilderProvider::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS));
};
