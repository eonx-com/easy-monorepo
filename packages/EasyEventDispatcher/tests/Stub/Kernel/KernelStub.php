<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Stub\Kernel;

use EonX\EasyEventDispatcher\Bundle\EasyEventDispatcherBundle;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            SymfonyEventDispatcherInterface::class,
            new Definition(SymfonyEventDispatcher::class)
        );
        $container->setDefinition(
            EventDispatcherStub::class,
            (new Definition(EventDispatcherStub::class))
                ->setDecoratedService(EventDispatcherInterface::class)
                ->setArgument('$decorated', new Reference('.inner'))
        );

        foreach ($container->getDefinitions() as $def) {
            $def->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyEventDispatcherBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
