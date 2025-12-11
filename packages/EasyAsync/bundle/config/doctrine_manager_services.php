<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\BundleParam;
use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use EonX\EasyAsync\Doctrine\Clearer\ManagersClearer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default managers clearer
    $services->set(ManagersClearer::class);

    // Default managers sanity checker
    $services
        ->set(ManagersSanityChecker::class)
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
