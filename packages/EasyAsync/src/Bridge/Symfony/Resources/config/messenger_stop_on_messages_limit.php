<?php
declare(strict_types=1);

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\StopWorkerOnMessagesLimitSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnMessagesLimitSubscriber::class)
        ->arg('$minMessages', '%' . BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MIN_MESSAGES . '%')
        ->arg('$maxMessages', '%' . BridgeConstantsInterface::PARAM_MESSENGER_WORKER_STOP_MAX_MESSAGES . '%')
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);
};
