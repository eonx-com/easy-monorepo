<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Decoder\JsonMessageBodyDecoder;
use EonX\EasyAsync\Messenger\Decoder\MessageBodyDecoderInterface;
use EonX\EasyAsync\Messenger\Subscriber\ShouldKillWorkerSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(MessageBodyDecoderInterface::class, JsonMessageBodyDecoder::class);

    $services
        ->set(ShouldKillWorkerSubscriber::class)
        ->tag('monolog.logger', ['channel' => ConfigParam::LogChannel->value]);
};
