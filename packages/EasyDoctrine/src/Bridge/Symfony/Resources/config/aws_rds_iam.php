<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds\AuthTokenConnectionFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BridgeConstantsInterface::SERVICE_AWS_RDS_IAM_CACHE, ArrayAdapter::class);

    $services
        ->set(AuthTokenConnectionFactory::class)
        ->decorate('doctrine.dbal.connection_factory')
        ->arg('$factory', service('.inner'))
        ->arg('$cache', service(BridgeConstantsInterface::SERVICE_AWS_RDS_IAM_CACHE))
        ->arg('$awsRegion', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_REGION))
        ->arg('$awsUsername', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_USERNAME))
        ->arg('$cacheExpiryInSeconds', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_CACHE_EXPIRY_IN_SECONDS))
        ->arg('$sslEnabled', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_SSL_ENABLED))
        ->arg('$sslMode', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_SSL_MODE))
        ->arg('$sslCertDir', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_SSL_CERT_DIR));
};
