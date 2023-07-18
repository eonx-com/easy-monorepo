<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SymfonyUidUuidV4Generator::class);

    $services->set(RamseyUuidV4Generator::class);

    $services->alias('easy_random.symfony_uuid4', SymfonyUidUuidV4Generator::class);

    $services->alias('easy_random.ramsey_uuid4', RamseyUuidV4Generator::class);

    $services->set(RandomGeneratorInterface::class, RandomGenerator::class);
};
