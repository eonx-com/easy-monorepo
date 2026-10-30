<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Provider\ArrayHandlerConfigProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set(ArrayHandlerConfigProvider::class)
        ->arg('$handlers', []);
};
