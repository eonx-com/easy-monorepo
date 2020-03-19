<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Symfony\Stubs;

use EonX\EasyPagination\Bridge\Symfony\EasyPaginationBundle;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPsr7Factory\Bridge\Symfony\EasyPsr7FactoryBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private static $request;

    /**
     * @var string
     */
    private $config;

    public function __construct(string $config)
    {
        $this->config = $config;

        parent::__construct('test', true);
    }

    public static function createRequestStack(): RequestStack
    {
        $requestStack = new RequestStack();

        if (static::$request !== null) {
            $requestStack->push(static::$request);
        }

        return $requestStack;
    }

    public static function setRequest(Request $request): void
    {
        static::$request = $request;
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getAlias(StartSizeDataResolverInterface::class)->setPublic(true);

        $requestStackDef = new Definition(RequestStack::class);
        $requestStackDef->setFactory([static::class, 'createRequestStack']);

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
        yield new EasyPaginationBundle();
        yield new EasyPsr7FactoryBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->config);
    }
}
