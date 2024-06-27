<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stub\Kernel;

use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private array $configs;

    public function __construct(string $environment, bool $debug, ?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->configs as $key => $config) {
            if (\is_string($key)) {
                $container->setParameter($key, $config);
            }
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyUtilsBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $key => $config) {
            if (\is_string($key) === false) {
                $loader->load($config);
            }
        }
    }
}
