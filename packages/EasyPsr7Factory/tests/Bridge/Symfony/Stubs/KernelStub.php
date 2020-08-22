<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs;

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
     * @var null|string[]
     */
    private $configs;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @param null|string[] $configs
     */
    public function __construct(string $environment, bool $debug, ?array $configs = null, ?Request $request = null)
    {
        $this->configs = $configs;
        $this->request = $request;

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        $requestStackDef = new Definition(RequestStack::class);

        if ($this->request !== null) {
            $requestStackDef->addMethodCall('push', [$this->request]);
        }

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
