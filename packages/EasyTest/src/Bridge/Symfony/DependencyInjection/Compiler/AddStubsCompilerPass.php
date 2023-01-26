<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyTest\Bridge\BridgeConstantsInterface;
use EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MessageLoggerListenerStub;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddStubsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            $container->getParameter(BridgeConstantsInterface::PARAM_ENABLE_MESSAGE_LOGGER_LISTENER_STUB)
            && $container->hasDefinition('mailer.message_logger_listener')
        ) {
            $container->getDefinition('mailer.message_logger_listener')
                ->setClass(MessageLoggerListenerStub::class)
                ->clearTag('kernel.reset');
        }
    }
}
