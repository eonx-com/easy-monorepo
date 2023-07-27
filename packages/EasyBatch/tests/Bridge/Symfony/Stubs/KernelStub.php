<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Bridge\Symfony\EasyBatchSymfonyBundle;
use EonX\EasyEncryption\Bridge\Symfony\EasyEncryptionSymfonyBundle;
use EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle;
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
        yield new EasyBatchSymfonyBundle();
        yield new EasyEncryptionSymfonyBundle();
        yield new EasyEventDispatcherSymfonyBundle();
        yield new EasyRandomSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
