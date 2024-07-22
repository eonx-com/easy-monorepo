<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Factory\AwsRdsAuthTokenConnectionFactory;
use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProvider;
use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AwsRdsConnectionParamsResolver::class)
        ->arg('$authTokenProvider', service(AwsRdsAuthTokenProvider::class)->nullOnInvalid())
        ->arg('$sslMode', param(ConfigParam::AwsRdsSslMode->value))
        ->arg('$certificateAuthorityProvider', service(AwsRdsCertificateAuthorityProvider::class)->nullOnInvalid())
        ->arg('$awsUsername', param(ConfigParam::AwsRdsIamAwsUsername->value));

    $services
        ->set(AwsRdsAuthTokenConnectionFactory::class)
        ->decorate('doctrine.dbal.connection_factory')
        ->arg('$factory', service('.inner'))
        ->arg('$connectionParamsResolver', service(AwsRdsConnectionParamsResolver::class));
};
