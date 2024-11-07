<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\CacheWarmer\AwsRdsCertificateAuthorityCacheWarmer;
use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AwsRdsCertificateAuthorityProvider::class)
        ->arg('$caPath', param(ConfigParam::AwsRdsSslCaPath->value))
        ->arg('$logger', service(ConfigParam::AwsRdsSslLogger->value)->nullOnInvalid());

    $services->set(AwsRdsCertificateAuthorityCacheWarmer::class);
};
