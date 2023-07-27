<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;
use EonX\EasyDoctrine\Bridge\BridgeConstantsInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(CertificateAuthorityProvider::class)
        ->arg('$caPath', param(BridgeConstantsInterface::PARAM_AWS_RDS_SSL_CA_PATH));
};
