<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stub\HttpKernel;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Bundle\EasyBatchBundle;
use EonX\EasyBatch\Tests\Stub\ConnectionFactory\DoctrineDbalConnectionFactoryStub;
use EonX\EasyBatch\Tests\Stub\EventDispatcher\SymfonyEventDispatcherStub;
use EonX\EasyBatch\Tests\Stub\MessageBus\MessageBusStub;
use EonX\EasyEncryption\Bridge\Symfony\EasyEncryptionSymfonyBundle;
use EonX\EasyEventDispatcher\Bundle\EasyEventDispatcherBundle;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyRandom\Bridge\Symfony\EasyRandomSymfonyBundle;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setAlias(ContainerInterface::class, 'service_container');
        $container->setDefinition(EventDispatcherInterface::class, new Definition(SymfonyEventDispatcherStub::class));
        $container->setDefinition(LockServiceInterface::class, new Definition(stdClass::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusStub::class));

        $container->setDefinition(
            Connection::class,
            (new Definition(Connection::class))->setFactory([DoctrineDbalConnectionFactoryStub::class, 'create'])
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
        yield new EasyBatchBundle();
        yield new EasyEncryptionSymfonyBundle();
        yield new EasyEventDispatcherBundle();
        yield new EasyRandomSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
