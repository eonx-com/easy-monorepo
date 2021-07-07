<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(BasicsConfigurator::class)
        ->arg('$projectRoot', '%' . BridgeConstantsInterface::PARAM_PROJECT_ROOT . '%')
        ->arg('$stripPath', '%' . BridgeConstantsInterface::PARAM_STRIP_PATH . '%')
        ->arg('$releaseStage', '%' . BridgeConstantsInterface::PARAM_RELEASE_STAGE . '%');

    $services
        ->set(RuntimeVersionConfigurator::class)
        ->arg('$runtime', '%' . BridgeConstantsInterface::PARAM_RUNTIME . '%')
        ->arg('$version', '%' . BridgeConstantsInterface::PARAM_RUNTIME_VERSION . '%');
};
