<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Bridge\Symfony\EasyApiTokenBundle;
use EonX\EasyPsr7Factory\Bridge\Symfony\EasyPsr7FactoryBundle;
use EonX\EasySecurity\Bridge\Symfony\EasySecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $configs;

    /**
     * @param null|string[] $configs
     */
    public function __construct(string $environment, bool $debug, ?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyPsr7FactoryBundle();
        yield new EasyApiTokenBundle();
        yield new EasySecurityBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../Fixtures/config/default.yaml');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }
}
