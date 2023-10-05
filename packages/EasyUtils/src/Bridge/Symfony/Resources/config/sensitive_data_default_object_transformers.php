<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\SensitiveData\ObjectTransformers\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\ObjectTransformers\ExceptionObjectTransformer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ExceptionObjectTransformer::class)
        ->arg('$priority', 100);

    $services
        ->set(DefaultObjectTransformer::class)
        ->arg('$priority', 10000);
};
