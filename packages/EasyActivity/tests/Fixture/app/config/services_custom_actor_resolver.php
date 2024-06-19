<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Common\Resolver\ActorResolverInterface;
use EonX\EasyActivity\Tests\Fixture\App\ActorResolver\CustomActorResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ActorResolverInterface::class, CustomActorResolver::class);
};
