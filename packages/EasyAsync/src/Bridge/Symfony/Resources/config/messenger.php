<?php

declare(strict_types=1);

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\MessageBodyDecoderInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\JsonMessageBodyDecoder;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ShouldKillWorkerSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(MessageBodyDecoderInterface::class, JsonMessageBodyDecoder::class);

    $services
        ->set(ShouldKillWorkerSubscriber::class)
        ->arg('$logger', ref(LoggerInterface::class));
};
