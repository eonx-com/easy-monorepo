<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php',
        __DIR__ . '/vendor/nesbot/carbon/src/Carbon/Traits/Localization.php',
    ]);

     $services = $containerConfigurator->services();
     $services->set(FinalizeClassesWithoutChildrenRector::class);
};
