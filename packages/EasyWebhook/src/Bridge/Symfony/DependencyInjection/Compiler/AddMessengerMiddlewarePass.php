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

        \array_push($middlewareReferences, new Reference(RetrySendWebhookMiddleware::class));

        $middleware->setValues($middlewareReferences);
    }
}
