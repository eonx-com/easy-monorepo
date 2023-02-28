<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyTest\Bridge\BridgeConstantsInterface;
use EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MailerMessageLoggerListenerStub;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddStubsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            $container->getParameter(BridgeConstantsInterface::PARAM_MAILER_MESSAGE_LOGGER_LISTENER_STUB_ENABLED)
        ) {
            $container->getDefinition('mailer.message_logger_listener')
                ->setClass(MailerMessageLoggerListenerStub::class)
                ->clearTag('kernel.reset');
        }
    }
}
