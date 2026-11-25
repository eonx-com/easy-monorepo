<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Routing\Warmer\RouteCollectionWarmer;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(SnapStartLifecycleSubscriber::class)
        ->arg('$warmers', tagged_iterator(SnapStartTag::Warmer->value))
        ->arg('$afterRestoreHooks', tagged_iterator(SnapStartTag::AfterRestoreHook->value))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->public(); // Must be public as EasyServerlessBundle::boot() retrieves it from the container

    $services
        ->set(RouteCollectionWarmer::class)
        ->arg('$router', service('router')->nullOnInvalid())
        ->tag(SnapStartTag::Warmer->value, ['priority' => 100]);
};
