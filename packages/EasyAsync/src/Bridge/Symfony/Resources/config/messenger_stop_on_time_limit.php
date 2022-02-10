<?php

declare(strict_types=1);

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\StopWorkerOnTimeLimitSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnTimeLimitSubscriber::class)
        ->arg('$minTimeLimitInSeconds', '%' . BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MIN_TIME . '%')
        ->arg('$maxTimeLimitInSeconds', '%' . BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MAX_TIME . '%')
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);
};
