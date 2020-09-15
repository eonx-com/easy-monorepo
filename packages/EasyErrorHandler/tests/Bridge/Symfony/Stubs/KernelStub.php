<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerBundle;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use EonX\EasyLogging\Bridge\Symfony\EasyLoggingBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $configs;

    /**
     * @param null|mixed[] $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(Client::class, new Definition(BugsnagClientStub::class));
        $container->setDefinition(TranslatorInterface::class, new Definition(TranslatorStub::class));

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
        yield new EasyLoggingBundle();
        yield new EasyErrorHandlerBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
