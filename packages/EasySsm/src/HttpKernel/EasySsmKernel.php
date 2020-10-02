<?php

declare(strict_types=1);

namespace EonX\EasySsm\HttpKernel;

use EonX\EasyAwsCredentialsFinder\Bridge\Symfony\EasyAwsCredentialsFinderBundle;
use EonX\EasyRandom\RandomGenerator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;

final class EasySsmKernel extends Kernel implements CompilerPassInterface
{
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

        parent::__construct(\sprintf('easy_ssm_%s', $this->getKernelId()), false);
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
        $loader->load(__DIR__ . '/../../config/services.php');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }

    private function getKernelId(): string
    {
        return (string)(new RandomGenerator())->randomString(8)->userFriendly();
    }
}
