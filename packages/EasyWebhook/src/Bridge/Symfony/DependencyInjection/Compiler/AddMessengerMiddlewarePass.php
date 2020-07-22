<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Bridge\Symfony\Messenger\RetrySendWebhookMiddleware;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddMessengerMiddlewarePass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const RETRY_LISTENER_ID = 'messenger.retry.send_failed_message_for_retry_listener';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_BUS) === false ||
            $container->hasDefinition($container->getParameter(BridgeConstantsInterface::PARAM_BUS)) === false) {
            return;
        }

        $busDef = $container->getDefinition($container->getParameter(BridgeConstantsInterface::PARAM_BUS));
        $middleware = $busDef->getArgument(0);

        if (($middleware instanceof IteratorArgument) === false) {
            return;
        }

        $middlewareReferences = $middleware->getValues();
        $middlewareReferences[] = new Reference(RetrySendWebhookMiddleware::class);

        $middleware->setValues($middlewareReferences);

        // Use senders locator from retry listener to fetch transport in retry middleware
        if ($container->hasDefinition(self::RETRY_LISTENER_ID)) {
            $def = $container->getDefinition(self::RETRY_LISTENER_ID);

            $container
                ->getDefinition(RetrySendWebhookMiddleware::class)
                ->setArgument('$container', $def->getArgument(0));
        }
    }
}
