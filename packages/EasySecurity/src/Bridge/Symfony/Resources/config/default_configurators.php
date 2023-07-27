<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Configurators\ApiTokenConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiTokenConfigurator::class)
        ->arg('$apiTokenDecoderFactory', service(ApiTokenDecoderFactoryInterface::class))
        ->arg('$apiTokenDecoder', param(BridgeConstantsInterface::PARAM_TOKEN_DECODER))
        ->arg('$priority', SecurityContextConfiguratorInterface::SYSTEM_PRIORITY);
};
