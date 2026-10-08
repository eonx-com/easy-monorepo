<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stub\Kernel;

use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    private readonly array $bundleList;

    /**
     * @var string[]
     */
    private readonly array $configs;

    /**
     * @param string[]|null $configs
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[]|null $bundles
     */
    public function __construct(?array $configs = null, ?array $bundles = null, ?string $environment = null)
    {
        $this->configs = $configs ?? [];
        $this->bundleList = $bundles ?? [new EasyLoggingBundle(), new EasyUtilsBundle()];

        parent::__construct($environment ?? 'test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        foreach ($container->getDefinitions() as $def) {
            $def->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield from $this->bundleList;
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
