<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\AwsRds\Iam\AuthTokenProvider;
use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BridgeConstantsInterface::SERVICE_AWS_RDS_IAM_CACHE, ArrayAdapter::class);

    $services
        ->set(AuthTokenProvider::class)
        ->arg('$awsRegion', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_REGION))
        ->arg(
            '$authTokenLifetimeInMinutes',
            param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AUTH_TOKEN_LIFETIME_IN_MINUTES)
        )
        ->arg('$cache', service(BridgeConstantsInterface::SERVICE_AWS_RDS_IAM_CACHE));
};
