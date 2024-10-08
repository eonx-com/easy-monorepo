<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\SensitiveData\Transformer\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\Transformer\ThrowableObjectTransformer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ThrowableObjectTransformer::class)
        ->arg('$priority', 100);

    $services
        ->set(DefaultObjectTransformer::class)
        ->arg('$priority', 10000);
};
