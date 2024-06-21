<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Stub\HttpKernel;

use EonX\EasyEventDispatcher\Bundle\EasyEventDispatcherBundle;
use EonX\EasyEventDispatcher\Tests\Stub\Dispatcher\SymfonyEventDispatcherStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(EventDispatcherInterface::class, new Definition(SymfonyEventDispatcherStub::class));

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
