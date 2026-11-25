<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Warmer\ClassMapWarmer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ClassMapWarmer::class)
        ->arg('$namespaces', param(ConfigParam::SnapStartClassMapWarmerNamespaces->value))
        ->tag(SnapStartTag::Warmer->value, ['priority' => 300]);
};
