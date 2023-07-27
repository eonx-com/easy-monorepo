<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Connection;
use EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle;
use EonX\EasyHttpClient\Bridge\Symfony\EasyHttpClientSymfonyBundle;
use EonX\EasyHttpClient\Tests\Stubs\LockServiceStub;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyWebhook\Bridge\Symfony\EasyWebhookSymfonyBundle;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
        // TODO: Find proper way to work with dbal connection
        $container->setDefinition('doctrine.dbal.default_connection', new Definition(MessageBusStub::class));
        $container->setDefinition(Connection::class, new Definition(MessageBusStub::class));

        $container->setDefinition(EventDispatcherInterface::class, new Definition(EventDispatcher::class));
        $container->setDefinition(LoggerInterface::class, new Definition(NullLogger::class));
        $container->setDefinition(LockServiceInterface::class, new Definition(LockServiceStub::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));

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
        yield new EasyEventDispatcherSymfonyBundle();
        yield new EasyWebhookSymfonyBundle();
        yield new EasyHttpClientSymfonyBundle();
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
