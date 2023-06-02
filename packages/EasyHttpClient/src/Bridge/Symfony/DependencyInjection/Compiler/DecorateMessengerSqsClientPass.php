<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\Symfony\Messenger\AmazonSqsTransportFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransportFactory as BaseAmazonSqsTransportFactory;

final class DecorateMessengerSqsClientPass extends AbstractEasyHttpClientCompilerPass
{
    private const MESSENGER_SQS_FACTORY = 'messenger.transport.sqs.factory';

    protected function doProcess(ContainerBuilder $container): void
    {
        if ($this->hasDefaultClient($container) === false
            || $container->has(self::MESSENGER_SQS_FACTORY) === false
            || \class_exists(BaseAmazonSqsTransportFactory::class) === false) {
            return;
        }

        $def = (new Definition(AmazonSqsTransportFactory::class))
            ->setAutoconfigured(true)
            ->setArgument('$httpClient', new Reference(self::DEFAULT_CLIENT_ID))
            ->setArgument('$logger', new Reference(
                LoggerInterface::class,
                ContainerInterface::IGNORE_ON_INVALID_REFERENCE
            ));

        $container->setDefinition(self::MESSENGER_SQS_FACTORY, $def);
    }

    protected function getEnableParamName(): string
    {
        return BridgeConstantsInterface::PARAM_DECORATE_MESSENGER_SQS_CLIENT;
    }
}
