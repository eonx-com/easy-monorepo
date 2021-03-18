<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Connection;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyWebhook\Bridge\Symfony\EasyWebhookBundle;
use EonX\EasyWebhook\Tests\Stubs\EventDispatcherStub;
use EonX\EasyWebhook\Tests\Stubs\LockServiceStub;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $configs;

    /**
     * @param null|string[] $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];

        parent::__construct('test', true);
    }

    public function process(ContainerBuilder $container): void
    {
        // TODO: Find proper way to work with dbal connection
        $container->setDefinition('doctrine.dbal.default_connection', new Definition(\EonX\EasyAsync\Tests\Stubs\EventDispatcherStub::class));
        $container->setDefinition(Connection::class, new Definition(EventDispatcherStub::class));

        $container->setDefinition(EventDispatcherInterface::class, new Definition(EventDispatcherStub::class));
        $container->setDefinition(LockServiceInterface::class, new Definition(LockServiceStub::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));

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
        yield new EasyWebhookBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
