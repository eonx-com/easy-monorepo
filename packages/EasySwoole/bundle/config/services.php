<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bundle\Enum\ConfigTag;
use EonX\EasySwoole\Caching\Factory\SwooleTableAdapterFactory;
use EonX\EasySwoole\Common\Listener\ApplicationStateCheckListener;
use EonX\EasySwoole\Common\Listener\ApplicationStateInitListener;
use EonX\EasySwoole\Common\Listener\ApplicationStateResetListener;
use EonX\EasySwoole\Common\Listener\SwooleDdListener;
use EonX\EasySwoole\Common\Listener\TrustedProxiesListener;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ApplicationStateInitListener::class)
        ->arg('$appStateInitializers', tagged_iterator(ConfigTag::AppStateInitializer->value))
        ->tag('kernel.event_listener', ['priority' => 50000]);

    $services
        ->set(ApplicationStateResetListener::class)
        ->arg('$appStateResetters', tagged_iterator(ConfigTag::AppStateResetter->value))
        ->tag('kernel.event_listener', ['priority' => -10000]);

    $services
        ->set(ApplicationStateCheckListener::class)
        ->arg('$appStateCheckers', tagged_iterator(ConfigTag::AppStateChecker->value))
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
