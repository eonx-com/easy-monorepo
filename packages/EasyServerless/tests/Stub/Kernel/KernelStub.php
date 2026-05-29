<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Kernel;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyServerless\Bundle\EasyServerlessBundle;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private readonly array $configs;

    /**
     * @var \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    private readonly array $extraBundles;

    /**
     * @param string[]|null $configs
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface[]|null $bundles
     */
    public function __construct(
        string $environment,
        bool $debug,
        ?array $configs = null,
        ?array $bundles = null,
    ) {
        $this->configs = $configs ?? [];
        $this->extraBundles = $bundles ?? [];

        parent::__construct($environment, $debug);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'kernel',
            (new Definition(KernelInterface::class))->setSynthetic(true)
        );
        $container->setAlias(KernelInterface::class, 'kernel');
        $container->setDefinition(BusDriver::class, new Definition(BusDriver::class));
        $container->setDefinition(NullLogger::class, new Definition(NullLogger::class));
        $container->setAlias('logger', NullLogger::class);
        $container->setDefinition(
            MessageBus::class,
            (new Definition(MessageBus::class))->setArguments([[]])
        );
        $container->setAlias(MessageBusInterface::class, MessageBus::class);
        $container->setDefinition(PhpSerializer::class, new Definition(PhpSerializer::class));
        $container->setAlias(SerializerInterface::class, PhpSerializer::class);
        $container->setDefinition(
            'messenger.retry_strategy_locator',
            (new Definition(ServiceLocator::class))->setArguments([[]])
        );
        $container->setDefinition(
            'services_resetter',
            (new Definition(ServicesResetter::class))->setArguments([new IteratorArgument([]), []])
        );

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyServerlessBundle();

        foreach ($this->extraBundles as $bundle) {
            yield $bundle;
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
