<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Stub\Kernel;

use EonX\EasyApiToken\Bundle\EasyApiTokenBundle;
use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Tests\Stub\Provider\DecoderProviderStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @param string[]|null $configs
     */
    public function __construct(
        private ?array $configs = null,
    ) {
        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }

        $container
            ->setDefinition(DecoderProviderStub::class, new Definition(DecoderProviderStub::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true)
            ->addTag(ConfigTag::DecoderProvider->value);
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiTokenBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs ?? [] as $config) {
            $loader->load($config);
        }
    }
}
