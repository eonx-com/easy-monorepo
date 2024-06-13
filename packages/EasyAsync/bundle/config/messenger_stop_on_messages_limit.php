<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\BundleParam;
use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnMessagesLimitSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StopWorkerOnMessagesLimitSubscriber::class)
        ->arg('$minMessages', param(ConfigParam::MessengerWorkerStopMinMessages->value))
        ->arg('$maxMessages', param(ConfigParam::MessengerWorkerStopMaxMessages->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
