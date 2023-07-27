<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\AppStateCheckers\RequestLimitsChecker;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RequestLimitsChecker::class)
        ->arg('$min', param(BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MIN))
        ->arg('$max', param(BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MAX));
};
