<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use EonX\EasyDoctrine\Bridge\AwsRds\Iam\AuthTokenProvider;
use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;
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
        ->set(AuthTokenProvider::class)
        ->arg('$awsRegion', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_REGION))
        ->arg(
            '$authTokenLifetimeInMinutes',
            param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AUTH_TOKEN_LIFETIME_IN_MINUTES)
        )
        ->arg('$cache', service(BridgeConstantsInterface::SERVICE_AWS_RDS_IAM_CACHE))
        ->arg('$awsUsername', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME));

    $services
        ->set(AwsRdsConnectionParamsResolver::class)
        ->arg('$authTokenProvider', service(AuthTokenProvider::class))
        ->arg('$sslMode', param(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE))
        ->arg('$certificateAuthorityProvider', service(CertificateAuthorityProvider::class)->nullOnInvalid());

    $services
        ->set(AuthTokenConnectionFactory::class)
        ->decorate('doctrine.dbal.connection_factory')
        ->arg('$factory', service('.inner'))
        ->arg('$connectionParamsResolver', service(AwsRdsConnectionParamsResolver::class));
};
