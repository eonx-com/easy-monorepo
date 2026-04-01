<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Kernel;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyServerless\Bundle\EasyServerlessBundle;
use EonX\EasyServerless\Tests\Stub\Resetter\ServicesResetterStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
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
     * @param array<int, class-string<BundleInterface>>|null $extraBundles
     * @param string[]|null $configs
     */
    public function __construct(
        string $environment,
        bool $debug,
        ?array $configs = null,
        private readonly ?array $extraBundles = null,
    ) {
        $this->configs = $configs ?? [];

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
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBus::class));
        $container->setDefinition(
            'messenger.retry_strategy_locator',
            new Definition(ServiceLocator::class, [[]])
        );
        $container->setDefinition(SerializerInterface::class, new Definition(PhpSerializer::class));
        $container->setDefinition('services_resetter', new Definition(ServicesResetterStub::class));

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

        foreach ($this->extraBundles ?? [] as $extraBundle) {
            yield new $extraBundle();
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
