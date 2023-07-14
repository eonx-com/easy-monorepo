<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony\Stubs;

use EonX\EasyRandom\Bridge\Symfony\EasyRandomSymfonyBundle;
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
        foreach ($container->getDefinitions() as $def) {
            $def->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyRandomSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
