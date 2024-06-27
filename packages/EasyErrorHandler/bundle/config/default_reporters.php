<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use EonX\EasyErrorHandler\Common\Provider\DefaultErrorReporterProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultErrorReporterProvider::class)
        ->arg('$ignoredExceptions', param(ConfigParam::LoggerIgnoredExceptions->value));
};
