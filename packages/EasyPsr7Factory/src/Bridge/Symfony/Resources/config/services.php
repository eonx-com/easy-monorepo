<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPsr7Factory\Bridge\Symfony\Factory\SymfonyPsr7RequestFactory;
use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(EasyPsr7FactoryInterface::class, EasyPsr7Factory::class);

    $services->set(SymfonyPsr7RequestFactory::class);

    $services
        ->set(ServerRequestInterface::class)
        ->factory([service(SymfonyPsr7RequestFactory::class), '__invoke']);
};
