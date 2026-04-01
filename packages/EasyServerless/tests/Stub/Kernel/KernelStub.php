<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Kernel;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyServerless\Bundle\EasyServerlessBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private readonly array $configs;

    /**
     * @param string[]|null $configs
     */
    public function __construct(
        string $environment,
        bool $debug,
        ?array $configs = null,
    ) {
        $this->configs = $configs ?? [];

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'kernel',
            (new Definition(KernelInterface::class))->setSynthetic(true)
        );
        $container->setAlias(KernelInterface::class, 'kernel');
        $container->setDefinition(BusDriver::class, new Definition(BusDriver::class));

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyServerlessBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
