<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Kernel;

use Doctrine\DBAL\Connection;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyWebhook\Bundle\EasyWebhookBundle;
use EonX\EasyWebhook\Tests\Stub\Dispatcher\EventDispatcherStub;
use EonX\EasyWebhook\Tests\Stub\Locker\LockerStub;
use EonX\EasyWebhook\Tests\Stub\MessageBus\MessageBusStub;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

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
        $container->setDefinition('doctrine.dbal.default_connection', new Definition(EventDispatcherStub::class));
        $container->setDefinition(Connection::class, new Definition(EventDispatcherStub::class));

        $container->setDefinition(EventDispatcherInterface::class, new Definition(EventDispatcherStub::class));
        $container->setDefinition(LockerInterface::class, new Definition(LockerStub::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));
        $container->setDefinition(LoggerInterface::class, new Definition(NullLogger::class));

        $container->setDefinition(UuidFactory::class, new Definition(UuidFactory::class));

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
