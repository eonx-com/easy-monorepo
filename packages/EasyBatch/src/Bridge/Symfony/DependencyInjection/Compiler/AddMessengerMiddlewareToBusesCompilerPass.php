<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyBatch\Bridge\Symfony\Messenger\DispatchBatchMiddleware;
use EonX\EasyBatch\Bridge\Symfony\Messenger\ProcessBatchItemMiddleware;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddMessengerMiddlewareToBusesCompilerPass implements CompilerPassInterface
{
    private const EASY_BATCH_MIDDLEWARE_LIST = [
        DispatchBatchMiddleware::class,
        ProcessBatchItemMiddleware::class,
    ];

    private const MESSENGER_BUS_TAG = 'messenger.bus';

    public function process(ContainerBuilder $container): void
    {
        foreach (\array_keys($container->findTaggedServiceIds(self::MESSENGER_BUS_TAG)) as $busId) {
            $busDef = $container->getDefinition($busId);
            $middleware = $busDef->getArgument(0);

            if (($middleware instanceof IteratorArgument) === false) {
                continue;
            }

            // Remove easy batch middleware if added in the app config
            $existingMiddlewareList = \array_filter($middleware->getValues(), static function (Reference $ref): bool {
                return \in_array((string)$ref, self::EASY_BATCH_MIDDLEWARE_LIST, true) === false;
            });

            // Add reference to easy batch middleware at the start of existing list
            \array_unshift($existingMiddlewareList, \array_map(static function (string $class): Reference {
                return new Reference($class);
            }, self::EASY_BATCH_MIDDLEWARE_LIST));

            // Replace
            $middleware->setValues($existingMiddlewareList);
        }
    }
}
