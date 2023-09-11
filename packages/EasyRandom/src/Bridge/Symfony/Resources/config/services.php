<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RandomIntegerGeneratorInterface::class, RandomIntegerGenerator::class);
    $services->set(RandomStringGeneratorInterface::class, RandomStringGenerator::class);
    $services->set(RandomGeneratorInterface::class, RandomGenerator::class);
};
