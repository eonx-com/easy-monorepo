<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\Console\Factory\OutputFactory;
use EonX\EasyServerless\Aws\Console\Factory\OutputFactoryInterface;
use EonX\EasyServerless\Aws\Console\Formatter\OutputMessageFormatterInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // MUST be public as it is fetched from the container in AbstractServerlessConsoleApplication
    $services
        ->set(OutputFactoryInterface::class, OutputFactory::class)
        ->arg('$messageFormatter', service(OutputMessageFormatterInterface::class)->nullOnInvalid())
        ->public();
};
