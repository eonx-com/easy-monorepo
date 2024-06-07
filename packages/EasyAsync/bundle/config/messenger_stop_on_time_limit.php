<?php
declare(strict_types=1);

use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnTimeLimitSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnTimeLimitSubscriber::class)
        ->arg('$minTimeLimitInSeconds', '%' . ConfigParam::MessengerWorkerStopMinTime->value . '%')
        ->arg('$maxTimeLimitInSeconds', '%' . ConfigParam::MessengerWorkerStopMaxTime->value . '%')
        ->tag('monolog.logger', ['channel' => ConfigParam::LogChannel->value]);
};
