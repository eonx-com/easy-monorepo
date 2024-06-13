<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EonX\\EasyAsync\\Tests\\Fixture\\App\\', '../src/*')
        ->exclude([
            '../src/Kernel/ApplicationKernel.php',
            '../src/**/ApiResource',
            '../src/**/CompilerPass',
            '../src/**/Config',
            '../src/**/DataTransferObject',
            '../src/**/Entity',
        ]);
};