<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\Warmer\SymfonyHttpHandlerWarmer;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Runs after the application warmers, which have the priority 0 by default
    $services
        ->set(SymfonyHttpHandlerWarmer::class)
        ->arg('$host', param(ConfigParam::SnapStartHttpRequestWarmerHost->value))
        ->arg('$path', param(ConfigParam::SnapStartHttpRequestWarmerPath->value))
        ->tag(SnapStartTag::Warmer->value, ['priority' => -100]);
};
