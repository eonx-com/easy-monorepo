<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Symfony\Listeners\StaticPhpFileListener;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(BridgeConstantsInterface::SERVICE_FILESYSTEM, Filesystem::class);

    $services
        ->set(StaticPhpFileListener::class)
        ->arg('$filesystem', service(BridgeConstantsInterface::SERVICE_FILESYSTEM))
        ->arg('$allowedDirs', param(BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_DIRS))
        ->arg('$allowedFilenames', param(BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_FILENAMES))
        ->tag('kernel.event_listener', ['priority' => 30000]);
};
