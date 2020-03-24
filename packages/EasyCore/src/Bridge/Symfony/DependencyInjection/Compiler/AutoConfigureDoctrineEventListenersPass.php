<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine\EntityEventDefinition;
use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine\EventDefinition;
use EonX\EasyCore\Bridge\Symfony\Interfaces\DoctrineEntityEventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\DoctrineEventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutoConfigureDoctrineEventListenersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityListeners = \array_keys($container->findTaggedServiceIds(TagsInterface::DOCTRINE_AUTOCONFIG_ENTITY_EVENT_LISTENER));

        foreach ($entityListeners as $entityListener) {
            $def = $container->getDefinition($entityListener);

            /** @var \EonX\EasyCore\Bridge\Symfony\Interfaces\DoctrineEntityEventListenerInterface $instance */
            $instance = $container->getReflectionClass($def->getClass())->newInstanceWithoutConstructor();
            $defaultEntity = $instance->registerEntityClass();

            foreach ($this->getDoctrineEntityEventDefinitions($instance) as $entityEventDefinition) {
                $def->addTag('doctrine.orm.entity_listener', $entityEventDefinition->getArguments($defaultEntity));
            }
        }


        $listeners = \array_keys($container->findTaggedServiceIds(TagsInterface::DOCTRINE_AUTOCONFIG_EVENT_LISTENER));

        foreach ($listeners as $listener) {
            $def = $container->getDefinition($listener);

            /** @var \EonX\EasyCore\Bridge\Symfony\Interfaces\DoctrineEventListenerInterface $instance */
            $instance = $container->getReflectionClass($def->getClass())->newInstanceWithoutConstructor();

            foreach ($this->getDoctrineEventDefinitions($instance) as $eventDefinition) {
                $def->addTag('doctrine.event_listener', $eventDefinition->getArguments());
            }
        }
    }

    /**
     * @return \EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine\EntityEventDefinition[]
     */
    private function getDoctrineEntityEventDefinitions(DoctrineEntityEventListenerInterface $listener): array
    {
        $filter = static function ($item): bool {
            return \is_string($item) || $item instanceof EntityEventDefinition;
        };

        $map = static function ($item): EntityEventDefinition {
            return \is_string($item) ? new EntityEventDefinition($item) : $item;
        };

        return \array_map($map, \array_filter($listener->registerEvents(), $filter));
    }

    /**
     * @return \EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine\EventDefinition[]
     */
    private function getDoctrineEventDefinitions(DoctrineEventListenerInterface $listener): array
    {
        $filter = static function ($item): bool {
            return \is_string($item) || $item instanceof EventDefinition;
        };

        $map = static function ($item): EventDefinition {
            return \is_string($item) ? new EventDefinition($item) : $item;
        };

        return \array_map($map, \array_filter($listener->registerEvents(), $filter));
    }
}
