<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DispatchBatchMiddleware;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ProcessBatchItemMiddleware;
use Nette\Utils\Strings;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddBatchMiddlewareToMessengerBusesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const MESSENGER_BUS_PREFIX = 'messenger.bus.';

    /**
     * @var string
     */
    private const MESSENGER_BUS_TAG = 'messenger.bus';

    public function process(ContainerBuilder $container): void
    {
        $buses = $this->getArrayParameter($container, BridgeConstantsInterface::PARAM_BATCH_MESSENGER_BUSES);
        $allBuses = \count($buses) < 1;

        // Allow to omit messenger bus prefix in config
        $buses = \array_map(static function (string $busId): string {
            return Strings::startsWith($busId, self::MESSENGER_BUS_PREFIX)
                ? $busId
                : \sprintf('%s%s', self::MESSENGER_BUS_PREFIX, $busId);
        }, $buses);
        
        foreach ($container->findTaggedServiceIds(self::MESSENGER_BUS_TAG) as $busId => $tags) {
            // Add middleware only to configured buses
            if ($allBuses === false && \in_array($busId, $buses, true) === false) {
                continue;
            }

            $busDef = $container->getDefinition($busId);
            $middleware = $busDef->getArgument(0);

            if (($middleware instanceof IteratorArgument) === false) {
                continue;
            }

            // Set our middleware to run first
            $newMiddlewareReferences = [
                new Reference(DispatchBatchMiddleware::class),
                new Reference(ProcessBatchItemMiddleware::class),
            ];

            // Add all existing middleware to the list
            foreach ($middleware->getValues() as $reference) {
                $newMiddlewareReferences[] = $reference;
            }

            // Replace middleware references on bus argument
            $middleware->setValues($newMiddlewareReferences);
        }
    }

    /**
     * @return mixed[]
     */
    private function getArrayParameter(ContainerBuilder $container, string $param): array
    {
        if ($container->hasParameter($param) === false) {
            return [];
        }

        $paramValue = $container->getParameter($param);

        return \is_array($paramValue) ? $paramValue : [];
    }
}
