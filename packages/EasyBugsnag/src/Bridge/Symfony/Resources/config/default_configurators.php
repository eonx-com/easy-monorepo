<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(BasicsConfigurator::class)
        ->arg('$projectRoot', '%kernel.project_dir%/src')
        ->arg('$stripPath', '%kernel.project_dir%')
        ->arg('$releaseStage', '%env(APP_ENV)%');

    $services
        ->set(RuntimeVersionConfigurator::class)
        ->arg('$runtime', 'symfony')
        ->arg('$version', Kernel::VERSION);
};
