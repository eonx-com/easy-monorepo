<?php

declare(strict_types=1);

namespace EonX\EasySsm\HttpKernel;

use EonX\EasyAwsCredentialsFinder\Bridge\Symfony\EasyAwsCredentialsFinderBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoReturnFactoryCompilerPass;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;

final class EasySsmKernel extends Kernel implements CompilerPassInterface
{
    use SimpleKernelTrait;

    /**
     * @var string[]
     */
    private $configs;

    /**
     * @param null|string[] $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct($this->getUniqueKernelKey(), false);
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/easy_ssm';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir() . '/easy_ssm_logs';
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $def) {
            $def->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyAwsCredentialsFinderBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/services.yaml');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AutoReturnFactoryCompilerPass())
            ->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
