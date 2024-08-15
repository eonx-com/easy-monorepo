<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle\CompilerPass;

use EonX\EasyLock\Bundle\Enum\ConfigParam;
use EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ReorderMessengerMiddlewareCompilerPass implements CompilerPassInterface
{
    private const EASY_LOCK_MIDDLEWARE_LIST = [
        ProcessWithLockMiddleware::class,
    ];

    private const MESSENGER_BUS_TAG = 'messenger.bus';

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container) === false) {
            return;
        }

        $enabledMiddlewareList = [];
        foreach (self::EASY_LOCK_MIDDLEWARE_LIST as $middleware) {
            if ($container->hasDefinition($middleware)) {
                $enabledMiddlewareList[] = $middleware;
            }
        }

        // No need to do anything if no middleware enabled
        if (\count($enabledMiddlewareList) < 1) {
            return;
        }

        // Convert easy lock middleware classes to reference
        $easyLockMiddlewareList = \array_map(
            static fn (string $class): Reference => new Reference($class),
            $enabledMiddlewareList
        );

        foreach (\array_keys($container->findTaggedServiceIds(self::MESSENGER_BUS_TAG)) as $busId) {
            $busDef = $container->getDefinition($busId);
            $middleware = $busDef->getArgument(0);

            if (($middleware instanceof IteratorArgument) === false) {
                continue;
            }

            // Remove easy lock middleware if added in the app config
            /** @var \Symfony\Component\DependencyInjection\Reference[] $existingMiddlewareList */
            $existingMiddlewareList = \array_filter(
                $middleware->getValues(),
                static fn (
                    Reference $ref,
                ): bool => \in_array((string)$ref, self::EASY_LOCK_MIDDLEWARE_LIST, true) === false
            );

            // Add reference to easy lock middleware at the start of existing list
            \array_unshift($existingMiddlewareList, ...$easyLockMiddlewareList);

            // Replace middleware list in bus argument
            $middleware->setValues($existingMiddlewareList);
        }
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        return $container->hasParameter(ConfigParam::MessengerMiddlewareEnabled->value)
            && $container->getParameter(ConfigParam::MessengerMiddlewareEnabled->value);
    }
}
