<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Stub\Kernel;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyTest\Bundle\EasyTestBundle;
use stdClass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // A decoration target for RegisterTraceableErrorHandlerStubCompilerPass, which expects
        // the EasyErrorHandler bundle to provide it; never instantiated in these tests
        if ($container->hasDefinition(ErrorHandlerInterface::class) === false) {
            $container->setDefinition(ErrorHandlerInterface::class, new Definition(stdClass::class));
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
        yield new EasyTestBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
