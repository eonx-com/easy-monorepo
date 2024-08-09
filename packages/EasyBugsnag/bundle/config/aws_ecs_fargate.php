<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Common\Configurator\AwsEcsFargateClientConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AwsEcsFargateClientConfigurator::class)
        ->arg('$storageFilename', param(ConfigParam::AwsEcsFargateMetaStorageFilename->value))
        ->arg('$url', param(ConfigParam::AwsEcsFargateMetaUrl->value));
};
