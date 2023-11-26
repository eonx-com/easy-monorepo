<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Symfony\Cache\SwooleTableAdapterFactory;
use EonX\EasySwoole\Bridge\Symfony\Listeners\ApplicationStateCheckListener;
use EonX\EasySwoole\Bridge\Symfony\Listeners\ApplicationStateInitListener;
use EonX\EasySwoole\Bridge\Symfony\Listeners\ApplicationStateResetListener;
use EonX\EasySwoole\Bridge\Symfony\Listeners\SwooleDdListener;
use EonX\EasySwoole\Bridge\Symfony\Listeners\TrustedProxiesListener;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ApplicationStateInitListener::class)
        ->arg('$appStateInitializers', tagged_iterator(BridgeConstantsInterface::TAG_APP_STATE_INITIALIZER))
        ->tag('kernel.event_listener', ['priority' => 50000]);

    $services
        ->set(ApplicationStateResetListener::class)
        ->arg('$appStateResetters', tagged_iterator(BridgeConstantsInterface::TAG_APP_STATE_RESETTER))
        ->tag('kernel.event_listener', ['priority' => -10000]);

    $services
        ->set(ApplicationStateCheckListener::class)
        ->arg('$appStateCheckers', tagged_iterator(BridgeConstantsInterface::TAG_APP_STATE_CHECKER))
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->tag('kernel.event_listener', ['priority' => -10001]);

    $services
        ->set(TrustedProxiesListener::class)
        ->arg('$container', service('service_container'))
        ->tag('kernel.event_listener', ['priority' => 20000]);

    $services
        ->set(SwooleDdListener::class)
        ->tag('kernel.event_listener', ['priority' => 30000]);

    $services
        ->set(SwooleTableAdapterFactory::class);
};
