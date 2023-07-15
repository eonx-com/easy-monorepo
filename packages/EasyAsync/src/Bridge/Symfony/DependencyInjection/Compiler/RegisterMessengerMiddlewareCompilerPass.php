<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersClearMiddleware;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersCloseConnectionMiddleware;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersSanityCheckMiddleware;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterMessengerMiddlewareCompilerPass implements CompilerPassInterface
{
    private const EASY_ASYNC_MIDDLEWARE_LIST = [
        DoctrineManagersSanityCheckMiddleware::class,
        DoctrineManagersClearMiddleware::class,
        DoctrineManagersCloseConnectionMiddleware::class,
    ];

    private const MESSENGER_BUS_TAG = 'messenger.bus';

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        $enabledMiddlewareList = [];
        foreach (self::EASY_ASYNC_MIDDLEWARE_LIST as $middleware) {
            if ($container->hasDefinition($middleware)) {
                $enabledMiddlewareList[] = $middleware;
            }
        }

        // No need to do anything if no middleware enabled
        if (\count($enabledMiddlewareList) < 1) {
            return;
        }

        // Convert easy async middleware classes to reference
        $easyAsyncMiddlewareList = \array_map(
            static fn (string $class): Reference => new Reference($class),
            $enabledMiddlewareList
        );

        foreach (\array_keys($container->findTaggedServiceIds(self::MESSENGER_BUS_TAG)) as $busId) {
            $busDef = $container->getDefinition($busId);
            $middleware = $busDef->getArgument(0);

            if (($middleware instanceof IteratorArgument) === false) {
                continue;
            }

            // Remove easy async middleware if added in the app config
            $existingMiddlewareList = \array_filter(
                $middleware->getValues(),
                static fn (
                    Reference $ref,
                ): bool => \in_array((string)$ref, self::EASY_ASYNC_MIDDLEWARE_LIST, true) === false
            );

            // Add reference to easy async middleware at the start of existing list
            \array_unshift($existingMiddlewareList, ...$easyAsyncMiddlewareList);

            /** @var \Symfony\Component\DependencyInjection\Reference[] $existingMiddlewareList */

            // Replace middleware list in bus argument
            $middleware->setValues($existingMiddlewareList);
        }
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(BridgeConstantsInterface::PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER)
            && $container->getParameter(BridgeConstantsInterface::PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER);
    }
}
