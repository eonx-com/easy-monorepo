<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Tests\Bridge\Symfony\Stubs;

use EonX\EasyAwsCredentialsFinder\Bridge\Symfony\EasyAwsCredentialsFinderBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(FilesystemFactoryStub::class, new Definition(FilesystemFactoryStub::class));

        $filesystemDef = new Definition(Filesystem::class);
        $filesystemDef->setFactory([new Reference(FilesystemFactoryStub::class), 'create']);

        $container->setDefinition(Filesystem::class, $filesystemDef);

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
        yield new EasyAwsCredentialsFinderBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
