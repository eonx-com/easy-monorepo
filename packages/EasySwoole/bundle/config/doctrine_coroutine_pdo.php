<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Doctrine\Factory\CoroutineConnectionFactory;
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
        ->arg('$defaultPoolSize', param(ConfigParam::DoctrineCoroutinePdoDefaultPoolSize->value))
        ->arg('$defaultHeartbeat', param(ConfigParam::DoctrineCoroutinePdoDefaultHeartbeat->value))
        ->arg('$defaultMaxIdleTime', param(ConfigParam::DoctrineCoroutinePdoDefaultMaxIdleTime->value))
        ->arg('$factory', service('.inner'))
        ->arg('$connectionParamsResolver', service(AwsRdsConnectionParamsResolver::class)->nullOnInvalid())
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid());
};
