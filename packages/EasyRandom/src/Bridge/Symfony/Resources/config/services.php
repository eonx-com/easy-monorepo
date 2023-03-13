<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\UuidV6\RamseyUuidV6Generator;
use EonX\EasyRandom\UuidV6\SymfonyUidUuidV6Generator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RandomGeneratorInterface::class, RandomGenerator::class)
        ->arg('$defaultUuidVersion', param(BridgeConstantsInterface::PARAM_DEFAULT_UUID_VERSION));

    // UUID v4 generators
    $services->set(SymfonyUidUuidV4Generator::class);
    $services->set(RamseyUuidV4Generator::class);

    $services->alias(BridgeConstantsInterface::SERVICE_SYMFONY_UUID4, SymfonyUidUuidV4Generator::class);
    $services->alias(BridgeConstantsInterface::SERVICE_RAMSEY_UUID4, RamseyUuidV4Generator::class);

    // UUID v6 generators
    $services->set(SymfonyUidUuidV6Generator::class);
    $services->set(RamseyUuidV6Generator::class);

    $services->alias(BridgeConstantsInterface::SERVICE_SYMFONY_UUID6, SymfonyUidUuidV6Generator::class);
    $services->alias(BridgeConstantsInterface::SERVICE_RAMSEY_UUID6, RamseyUuidV6Generator::class);
};
