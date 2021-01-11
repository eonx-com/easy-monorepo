<?php

declare(strict_types=1);

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Configurators\ApiTokenConfigurator;
use EonX\EasySecurity\Configurators\AuthorizationMatrixConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiTokenConfigurator::class)
        ->arg('$apiTokenDecoder', ref(BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER))
        ->arg('$priority', SecurityContextConfiguratorInterface::SYSTEM_PRIORITY);

    $services
        ->set(AuthorizationMatrixConfigurator::class)
        ->arg('$priority', SecurityContextConfiguratorInterface::SYSTEM_PRIORITY);
};
