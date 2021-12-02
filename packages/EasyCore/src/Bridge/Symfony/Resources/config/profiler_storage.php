<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Bridge\Symfony\Profiler\FlysystemProfilerStorage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default Flysystem implementation to local
    $services
        ->set('easy_core.profiler_storage_flysystem.local_adapter', Local::class)
        // Symfony default
        ->arg('$root', '%kernel.cache_dir%/profiler/');

    $services
        ->set(BridgeConstantsInterface::SERVICE_PROFILER_STORAGE_FLYSYSTEM, Filesystem::class)
        ->arg('$adapter', service('easy_core.profiler_storage_flysystem.local_adapter'));

    $services
        ->set(FlysystemProfilerStorage::class)
        ->arg('$filesystem', service(BridgeConstantsInterface::SERVICE_PROFILER_STORAGE_FLYSYSTEM));
};
