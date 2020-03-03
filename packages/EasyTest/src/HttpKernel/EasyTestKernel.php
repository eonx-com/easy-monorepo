<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;

final class EasyTestKernel extends Kernel
{
    /**
     * Get cache dir.
     *
     * @return string
     */
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/easy_test';
    }

    /**
     * Get log dir.
     *
     * @return string
     */
    public function getLogDir(): string
    {
        return \sys_get_temp_dir() . '/east_test_logs';
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        return [];
    }

    /**
     * Loads the container configuration.
     *
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     *
     * @return void
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/services.yaml');
    }

    /**
     * Build container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
