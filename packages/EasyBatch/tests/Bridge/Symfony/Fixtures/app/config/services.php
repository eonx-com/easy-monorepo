<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\EventDispatcher\EventDispatcherStub;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('EonX\\EasyBatch\\Tests\\Bridge\\Symfony\\Fixtures\\App\\', '../src/*')
        ->exclude([
            '../src/Kernel/ApplicationKernel.php',
        ]);

    $services->set(EventDispatcherStub::class)
        ->arg('$decorated', service('.inner'))
        ->decorate(EventDispatcherInterface::class);
};
