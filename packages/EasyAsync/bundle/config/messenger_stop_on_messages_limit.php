<?php
declare(strict_types=1);

use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnMessagesLimitSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnMessagesLimitSubscriber::class)
        ->arg('$minMessages', '%' . ConfigParam::MessengerWorkerStopMinMessages->value . '%')
        ->arg('$maxMessages', '%' . ConfigParam::MessengerWorkerStopMaxMessages->value . '%')
        ->tag('monolog.logger', ['channel' => ConfigParam::LogChannel->value]);
};
