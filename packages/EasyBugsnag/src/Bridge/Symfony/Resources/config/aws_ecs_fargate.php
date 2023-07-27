<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Configurators\AwsEcsFargateConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AwsEcsFargateConfigurator::class)
        ->arg('$storageFilename', '%' . BridgeConstantsInterface::PARAM_AWS_ECS_FARGATE_META_STORAGE_FILENAME . '%')
        ->arg('$url', '%' . BridgeConstantsInterface::PARAM_AWS_ECS_FARGATE_META_URL . '%');
};
