<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Bundle\Enum\ConfigServiceId;
use EonX\EasySwoole\Common\Listener\StaticPhpFileListener;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(ConfigServiceId::Filesystem->value, Filesystem::class);

    // @todo Change priority to 30010 in 7.0 to allow other listeners in the middle
    $services
        ->set(StaticPhpFileListener::class)
        ->arg('$filesystem', service(ConfigServiceId::Filesystem->value))
        ->arg('$allowedDirs', param(ConfigParam::StaticPhpFilesAllowedDirs->value))
        ->arg('$allowedFilenames', param(ConfigParam::StaticPhpFilesAllowedFilenames->value))
        ->tag('kernel.event_listener', ['priority' => 30000]);
};
