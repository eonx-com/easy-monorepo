<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/tests',
    ]);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests/rector_bootstrap.php',
    ]);
};
