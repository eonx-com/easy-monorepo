<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SensitiveDataSanitizerProcessor::class)
        ->arg('$sensitiveDataSanitizer', service(SensitiveDataSanitizerInterface::class));
};
