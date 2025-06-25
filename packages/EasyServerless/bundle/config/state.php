<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\State\Listener\CheckStateListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(CheckStateListener::class)
        ->arg('$stateCheckers', tagged_iterator(ConfigTag::StateChecker->value))
        ->tag('kernel.event_listener');
};
