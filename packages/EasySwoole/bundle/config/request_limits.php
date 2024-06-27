<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Common\Checker\RequestLimitsChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RequestLimitsChecker::class)
        ->arg('$min', param(ConfigParam::RequestLimitsMin->value))
        ->arg('$max', param(ConfigParam::RequestLimitsMax->value));
};
