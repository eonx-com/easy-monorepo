<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;

final class EasyTestKernel extends Kernel
{
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/easy_test';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir() . '/east_test_logs';
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/services.yaml');
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
