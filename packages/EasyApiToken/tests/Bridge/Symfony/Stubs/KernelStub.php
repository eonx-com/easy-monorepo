<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Bridge\Symfony\EasyApiTokenBundle;
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
        yield new EasyApiTokenBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config.yaml');
        $loader->load(__DIR__ . '/config_test.yaml');
    }
}
