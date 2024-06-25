<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Common\Middleware\IdHeaderMiddleware;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(IdHeaderMiddleware::class)
        ->arg('$idHeader', param(ConfigParam::IdHeader->value));
};
