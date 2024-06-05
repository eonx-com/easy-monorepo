<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\ActorResolver\Case4ActorResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();
    
    $services->set(ActorResolverInterface::class, Case4ActorResolver::class);
};
