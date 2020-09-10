<?php

declare(strict_types=1);

namespace EonX\EasyDocker\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EasyDockerKernel extends Kernel implements ExtraConfigAwareKernelInterface
{
    use SimpleKernelTrait;

    /**
     * @var string[]|\Symplify\SmartFileSystem\SmartFileInfo[]
     */
    private $configs = [];

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/parameters.yaml');
        $loader->load(__DIR__ . '/../../config/services.yaml');

        foreach ($this->configs as $config) {
            if ($config instanceof SmartFileInfo) {
                $config = $config->getPath();
            }

            $loader->load($config);
        }
    }

    /**
     * @param string[]|\Symplify\SmartFileSystem\SmartFileInfo[] $configs
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
