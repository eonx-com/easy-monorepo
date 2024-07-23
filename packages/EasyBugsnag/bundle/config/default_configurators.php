<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Configurator\BasicsClientConfigurator;
use EonX\EasyBugsnag\Configurator\RuntimeVersionClientConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(BasicsClientConfigurator::class)
        ->arg('$projectRoot', param(ConfigParam::ProjectRoot->value))
        ->arg('$stripPath', param(ConfigParam::StripPath->value))
        ->arg('$releaseStage', param(ConfigParam::ReleaseStage->value));

    $services
        ->set(RuntimeVersionClientConfigurator::class)
        ->arg('$runtime', param(ConfigParam::Runtime->value))
        ->arg('$version', param(ConfigParam::RuntimeVersion->value));
};
