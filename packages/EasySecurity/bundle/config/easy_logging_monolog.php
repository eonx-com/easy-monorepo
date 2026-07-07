<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySecurity\Bundle\Enum\BundleParam;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\SymfonySecurity\Factory\AuthenticationFailureResponseFactoryInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $loggerId = \sprintf('monolog.logger.%s', BundleParam::LogChannel->value);

    $services->get(SecurityContextResolverInterface::class)
        ->arg('$logger', service($loggerId));

    $services->get(AuthenticationFailureResponseFactoryInterface::class)
        ->arg('$logger', service($loggerId));
};
