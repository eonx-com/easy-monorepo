<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RandomIntegerGeneratorInterface::class, RandomIntegerGenerator::class);
    $services->set(RandomStringGeneratorInterface::class, RandomStringGenerator::class);
    $services->set(UuidGeneratorInterface::class, UuidGenerator::class);
    $services->set(RandomGeneratorInterface::class, RandomGenerator::class);
};
