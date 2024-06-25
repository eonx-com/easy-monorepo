<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Tests\Stub\Kernel;

use EonX\EasyTemplatingBlock\Bundle\EasyTemplatingBlockBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class KernelStub extends Kernel implements CompilerPassInterface
{
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

    public function process(ContainerBuilder $container): void
    {
        $this->setTwigDefinition($container);

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
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
        yield new EasyTemplatingBlockBundle();
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

    private function setTwigDefinition(ContainerBuilder $container): void
    {
        $twigLoaderDef = new Definition(FilesystemLoader::class);
        $twigLoaderDef->setArgument('$paths', [
            __DIR__ . '/../../Fixture/templates',
        ]);

        $twigDef = new Definition(Environment::class);
        $twigDef->setArgument('$loader', new Reference('twig.loader'));

        $container->setDefinition('twig.loader', $twigLoaderDef);
        $container->setDefinition(Environment::class, $twigDef);
    }
}
