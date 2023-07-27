<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use EonX\EasyDoctrine\Bridge\AwsRds\Iam\AuthTokenProvider;
use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;
use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;
use EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds\AuthTokenConnectionFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AwsRdsConnectionParamsResolver::class)
        ->arg('$authTokenProvider', service(AuthTokenProvider::class)->nullOnInvalid())
        ->arg('$sslMode', param(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_MODE))
        ->arg('$certificateAuthorityProvider', service(CertificateAuthorityProvider::class)->nullOnInvalid())
        ->arg('$awsUsername', param(BridgeConstantsInterface::PARAM_AWS_RDS_IAM_AWS_USERNAME));

    $services
        ->set(AuthTokenConnectionFactory::class)
        ->decorate('doctrine.dbal.connection_factory')
        ->arg('$factory', service('.inner'))
        ->arg('$connectionParamsResolver', service(AwsRdsConnectionParamsResolver::class));
};
