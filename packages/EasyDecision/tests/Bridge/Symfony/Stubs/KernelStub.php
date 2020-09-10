<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Symfony\Stubs;

use EonX\EasyDecision\Bridge\Symfony\EasyDecisionBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var null|string[]
     */
    private $configPaths;

    /**
     * @param null|string[] $configPaths
     */
    public function __construct(?array $configPaths = null)
    {
        $this->configPaths = $configPaths;

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new EasyDecisionBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if ($this->configPaths === null) {
            return;
        }

        foreach ($this->configPaths as $configPath) {
            $loader->load($configPath);
        }
    }
}
