<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO\CoroutineConnectionFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(CoroutineConnectionFactory::class)
        ->decorate('doctrine.dbal.connection_factory', priority: -1000) // Make sure it's the last decoration
        ->arg('$requestStack', service(RequestStack::class))
        ->arg('$defaultPoolSize', param(BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_POOL_SIZE))
        ->arg('$defaultHeartbeat', param(BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_HEARTBEAT))
        ->arg(
            '$defaultMaxIdleTime',
            param(BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_MAX_IDLE_TIME)
        )
        ->arg('$factory', service('.inner'))
        ->arg('$connectionParamsResolver', service(AwsRdsConnectionParamsResolver::class)->nullOnInvalid())
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid());
};
