<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiPlatform\Bridge\Symfony\EasyApiPlatformSymfonyBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var mixed[]
     */
    private array $configs;

    /**
     * @param mixed[]|null $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition('api_platform.iri_converter', new Definition(IriConverterStub::class));
        $container->setDefinition(
            'api_platform.serializer.context_builder',
            new Definition(SerializerContextBuilderStub::class)
        );
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiPlatformSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
