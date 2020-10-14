<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\Profiler\FlysystemProfilerStorage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ReplaceProfilerStoragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('profiler.storage') === false
            || $container->hasDefinition(FlysystemProfilerStorage::class) === false) {
            return;
        }

        $container->setDefinition('profiler.storage', $container->getDefinition(FlysystemProfilerStorage::class));
    }
}
