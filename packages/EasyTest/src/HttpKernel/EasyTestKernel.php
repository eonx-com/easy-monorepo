<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;

final class EasyTestKernel extends Kernel
{
    use SimpleKernelTrait;

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
     * Add compiler passes.
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
