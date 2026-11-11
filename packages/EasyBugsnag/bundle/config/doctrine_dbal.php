<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Doctrine\Logger\QueryBreadcrumbLogger;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(QueryBreadcrumbLogger::class)
        ->arg('$logQueryValues', param(ConfigParam::DoctrineDbalLogQueryValues->value));
};
