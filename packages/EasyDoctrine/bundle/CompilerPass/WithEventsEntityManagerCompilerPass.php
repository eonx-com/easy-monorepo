<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\CompilerPass;

use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class WithEventsEntityManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array<string, string> $connections */
        $connections = $container->getParameter('doctrine.connections');
        $connectionNames = \array_keys($connections);

        foreach ($connectionNames as $connectionName) {
            $entityManagerServiceId = 'doctrine.orm.' . $connectionName . '_entity_manager';

            if ($container->hasDefinition($entityManagerServiceId) === false) {
                continue;
            }

            $withEventsEntityManagerServiceId = 'easy_doctrine.' . $connectionName . '.with_events_entity_manager';
            $container
                ->register($withEventsEntityManagerServiceId, WithEventsEntityManager::class)
                ->setDecoratedService($entityManagerServiceId)
                ->setArgument(
                    '$deferredEntityEventDispatcher',
                    new Reference(DeferredEntityEventDispatcherInterface::class)
                )
                ->setArgument('$eventDispatcher', new Reference(EventDispatcherInterface::class))
                ->setArgument('$decorated', new Reference($withEventsEntityManagerServiceId . '.inner'));
        }
    }
}
