<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs;

use EonX\EasyPsr7Factory\Bridge\Symfony\EasyPsr7FactoryBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        $container
            ->setDefinition(ServiceStub::class, new Definition(ServiceStub::class))
            ->setAutowired(true)
            ->setPublic(true);
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyPsr7FactoryBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
