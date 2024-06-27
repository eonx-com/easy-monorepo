<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Stub\HttpKernel;

use EonX\EasyBugsnag\Bundle\EasyBugsnagBundle;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    private static ?Request $request = null;

    /**
     * @var string[]
     */
    private array $configs;

    /**
     * @param string[]|null $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

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
        yield new EasyBugsnagBundle();
        yield new EasyUtilsBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
