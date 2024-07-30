<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Kernel;

use EonX\EasyDecision\Bundle\EasyDecisionBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @param string[]|null $configPaths
     */
    public function __construct(
        private readonly ?array $configPaths = null,
    ) {
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
