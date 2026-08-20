<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bundle\Enum\BundleParam;
use EonX\EasyHttpClient\PsrLogger\Listener\LogHttpRequestSentListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->get(LogHttpRequestSentListener::class)
        ->arg('$logger', service(\sprintf('monolog.logger.%s', BundleParam::LogChannel->value)));
};
