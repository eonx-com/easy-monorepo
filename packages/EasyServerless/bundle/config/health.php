<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Health\RouteLoader\HealthCheckRouteLoader;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(HealthCheckController::class)
        ->arg('$checkers', tagged_iterator(ConfigTag::HealthChecker->value))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->tag('controller.service_arguments');

    $services
        ->set(HealthCheckRouteLoader::class)
        ->tag('routing.loader');
};
