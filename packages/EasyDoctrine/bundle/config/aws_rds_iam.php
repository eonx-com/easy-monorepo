<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProvider;
use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use EonX\EasyDoctrine\Bundle\Enum\ConfigServiceId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ConfigServiceId::AwsRdsIamCache->value, ArrayAdapter::class);

    $services
        ->set(AwsRdsAuthTokenProvider::class)
        ->arg('$awsRegion', param(ConfigParam::AwsRdsIamAwsRegion->value))
        ->arg(
            '$authTokenLifetimeInMinutes',
            param(ConfigParam::AwsRdsIamAuthTokenLifetimeInMinutes->value)
        )
        ->arg('$cache', service(ConfigServiceId::AwsRdsIamCache->value))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid());
};
