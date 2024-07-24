<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bundle\Enum\ConfigParam;
use EonX\EasySecurity\Common\Configurator\ApiTokenConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiTokenConfigurator::class)
        ->arg('$apiTokenDecoderFactory', service(ApiTokenDecoderFactoryInterface::class))
        ->arg('$apiTokenDecoder', param(ConfigParam::TokenDecoder->value))
        ->arg('$priority', param(ConfigParam::DefaultConfiguratorsPriority->value));
};
