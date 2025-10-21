<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\CompilerPass;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyEventDispatcherPublicCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (\interface_exists(EventDispatcherInterface::class) === false) {
            return;
        }

        if ($container->hasAlias(EventDispatcherInterface::class)) {
            $container->getAlias(EventDispatcherInterface::class)
                ->setPublic(true);
        }

        if ($container->hasDefinition(EventDispatcherInterface::class)) {
            $container->getDefinition(EventDispatcherInterface::class)
                ->setPublic(true);
        }
    }
}
