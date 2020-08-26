<?php

declare(strict_types=1);

use EonX\EasySsm\Services\Dotenv\EnvLoaderInterface;
use EonX\EasySsm\Services\Dotenv\Loaders\DotenvLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(EnvLoaderInterface::class, DotenvLoader::class);
};
