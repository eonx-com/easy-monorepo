<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnTimeLimitSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnTimeLimitSubscriber::class)
        ->arg('$minTimeLimitInSeconds', param(ConfigParam::MessengerWorkerStopMinTime->value))
        ->arg('$maxTimeLimitInSeconds', param(ConfigParam::MessengerWorkerStopMaxTime->value))
        ->tag('monolog.logger', ['channel' => ConfigParam::LogChannel->value]);
};
