<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Bridge\Symfony\Profiler\FlysystemProfilerStorage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default Flysystem implementation to local
    $services
        ->set('easy_core.profiler_storage_flysystem.local_adapter', Local::class)
        ->arg('$root', '%kernel.cache_dir%/profiler/'); // Symfony default

    $services
        ->set(BridgeConstantsInterface::SERVICE_PROFILER_STORAGE_FLYSYSTEM, Filesystem::class)
        ->arg('$adapter', ref('easy_core.profiler_storage_flysystem.local_adapter'));

    $services
        ->set(FlysystemProfilerStorage::class)
        ->arg('$filesystem', ref(BridgeConstantsInterface::SERVICE_PROFILER_STORAGE_FLYSYSTEM));
};
