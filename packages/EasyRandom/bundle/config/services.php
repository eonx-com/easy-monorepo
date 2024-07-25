<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRandom\Bundle\Enum\ConfigParam;
use EonX\EasyRandom\Bundle\Enum\ConfigServiceId;
use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RandomIntegerGeneratorInterface::class, RandomIntegerGenerator::class);

    $services->set(RandomStringGeneratorInterface::class, RandomStringGenerator::class);

    $services->set(ConfigServiceId::UuidFactory->value, UuidFactory::class)
        ->arg('$defaultClass', param(ConfigParam::UuidVersion->value));
    $services->set(UuidGeneratorInterface::class, UuidGenerator::class)
        ->arg('$uuidFactory', service(ConfigServiceId::UuidFactory->value));

    $services->set(RandomGeneratorInterface::class, RandomGenerator::class);
};
