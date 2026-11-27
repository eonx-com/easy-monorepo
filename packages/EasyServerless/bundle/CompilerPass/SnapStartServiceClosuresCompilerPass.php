<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\CompilerPass;

use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Gives the warmers and the after restore hooks to the SnapStartLifecycleSubscriber as closures that create them, in
 * the order of the tagged iterators: one that cannot be created does not stop the iteration, so the next ones still
 * run, e.g. the ConnectionsWarmer, which closes the connections before the snapshot.
 */
final class SnapStartServiceClosuresCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(SnapStartLifecycleSubscriber::class) === false) {
            return;
        }

        $definition = $container->getDefinition(SnapStartLifecycleSubscriber::class);

        foreach ($definition->getArguments() as $index => $argument) {
            if ($argument instanceof TaggedIteratorArgument === false) {
                continue;
            }

            $closures = [];

            foreach ($argument->getValues() as $reference) {
                $closures[(string)$reference] = new ServiceClosureArgument($reference);
            }

            // A plain array: debug:container expects only references in an IteratorArgument
            $definition->replaceArgument($index, $closures);
        }
    }
}
