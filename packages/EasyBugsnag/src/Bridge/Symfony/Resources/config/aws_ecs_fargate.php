<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Configurators\AwsEcsFargateConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
