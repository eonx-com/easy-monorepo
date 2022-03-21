<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Symfony\Stubs;

use EonX\EasyPagination\Bridge\Symfony\EasyPaginationSymfonyBundle;
use EonX\EasyPsr7Factory\Bridge\Symfony\EasyPsr7FactorySymfonyBundle;
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
     * @var null|string
     */
    private $config;

    public function __construct(?string $config = null)
    {
        $this->config = $config;

        parent::__construct('test', true);
    }

    public static function createRequestStack(): RequestStack
    {
        $requestStack = new RequestStack();

        if (self::$request !== null) {
            $requestStack->push(self::$request);
        }

        return $requestStack;
    }

    public static function setRequest(Request $request): void
    {
        self::$request = $request;
    }

    public function process(ContainerBuilder $container): void
    {
        $requestStackDef = new Definition(RequestStack::class);
        $requestStackDef->setFactory([self::class, 'createRequestStack']);

        $container->setDefinition(RequestStack::class, $requestStackDef);

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
        yield new EasyPaginationSymfonyBundle();
        yield new EasyPsr7FactorySymfonyBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if ($this->config !== null) {
            $loader->load($this->config);
        }
    }
}
