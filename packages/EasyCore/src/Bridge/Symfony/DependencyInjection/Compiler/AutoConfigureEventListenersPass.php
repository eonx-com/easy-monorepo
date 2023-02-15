<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutoConfigureEventListenersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $listeners = \array_keys($container->findTaggedServiceIds(TagsInterface::EVENT_LISTENER_AUTO_CONFIG));

        foreach ($listeners as $listener) {
            $def = $container->getDefinition($listener);

            /** @var \EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface $instance */
            $instance = $container->getReflectionClass($def->getClass())
                ->newInstanceWithoutConstructor();

            foreach ($instance->registerEvents() as $eventTag) {
                $def->addTag($eventTag->getName(), $eventTag->getAttributes());
            }
        }
    }
}
