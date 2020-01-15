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
    /**
     * KernelStub constructor.
     */
    public function __construct()
    {
        parent::__construct('test', true);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $container
            ->setDefinition(ServiceStub::class, new Definition(ServiceStub::class))
            ->setAutowired(true)
            ->setPublic(true);
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|\Symfony\Component\HttpKernel\Bundle\BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles(): iterable
    {
        yield new EasyPsr7FactoryBundle();
    }

    /**
     * Loads the container configuration.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
