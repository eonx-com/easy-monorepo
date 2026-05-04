<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasySecurity\Bundle\Enum\BundleParam;
use EonX\EasySecurity\Bundle\Enum\ConfigServiceId;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\SymfonySecurity\Factory\AuthenticationFailureResponseFactoryInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Logger
    $services
        ->set(ConfigServiceId::Logger->value, LoggerInterface::class)
        ->factory([service(LoggerFactoryInterface::class), 'create'])
        ->args([BundleParam::LogChannel->value]);

    $services->get(SecurityContextResolverInterface::class)
        ->arg('$logger', service(ConfigServiceId::Logger->value));

    $services->get(AuthenticationFailureResponseFactoryInterface::class)
        ->arg('$logger', service(ConfigServiceId::Logger->value));
};
