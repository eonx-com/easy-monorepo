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
    private const RETRY_LISTENER_ID = 'messenger.retry.send_failed_message_for_retry_listener';

    public function process(ContainerBuilder $container): void
    {
        $busParam = $this->getParameter($container, BridgeConstantsInterface::PARAM_BUS);

        if (\is_string($busParam) === false || $busParam === '') {
            return;
        }

        // If bus definition not defined, abort
        if ($container->hasDefinition($busParam) === false) {
            return;
        }

        $busDef = $container->getDefinition($busParam);
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

    private function getParameter(ContainerBuilder $container, string $param): ?string
    {
        if ($container->hasParameter($param) === false) {
            return null;
        }

        $value = $container->getParameter($param);

        return \is_string($value) ? $value : null;
    }
}
