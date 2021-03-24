<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Bridge\Symfony\EasyApiTokenSymfonyBundle;
use EonX\EasyApiToken\Tests\Stubs\DecoderProviderStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function __construct()
    {
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
            ->addTag(BridgeConstantsInterface::TAG_DECODER_PROVIDER);
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiTokenSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
