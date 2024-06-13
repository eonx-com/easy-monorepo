<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Tests\Fixture\App\CompilerPass\AddNotSupportedEntityManagerCompilerPass;
use EonX\EasyAsync\Tests\Fixture\App\ObjectManager\NotSupportedObjectManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $containerBuilder->addCompilerPass(new AddNotSupportedEntityManagerCompilerPass());

    $services->set(NotSupportedObjectManager::class)
        ->public();
};