<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyMonorepo\Console\MonorepoApplication;
use EonX\EasyMonorepo\MonorepoKernel;
use Symfony\Component\Console\Command\Command;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services
        ->instanceof(Command::class)
        ->tag(MonorepoApplication::TAG_COMMAND);

    $services
        ->load(MonorepoKernel::NAMESPACE, __DIR__ . '/../monorepo')
        ->exclude([
            __DIR__ . '/../monorepo/Release/*',
            __DIR__ . '/../monorepo/MonorepoKernel.php',
        ]);

    $services
        ->set(MonorepoApplication::class)
        ->arg('$commands', tagged_iterator(MonorepoApplication::TAG_COMMAND));
};
