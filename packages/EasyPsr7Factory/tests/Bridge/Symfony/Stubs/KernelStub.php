<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs;

use EonX\EasyPsr7Factory\Bridge\Symfony\EasyPsr7FactoryBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $requestDef = (new Definition(Request::class))
            ->setFactory([Request::class, 'create'])
            ->setArguments(['http://eonx.com']);

        $requestStackDef = (new Definition(RequestStack::class))
            ->addMethodCall('push', [new Reference('request')]);

        $container->setDefinition('request', $requestDef);
        $container->setDefinition(RequestStack::class, $requestStackDef);

        foreach ($container->getDefinitions() as $def) {
            $def->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyPsr7FactoryBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
