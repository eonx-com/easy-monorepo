<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Bridge\Symfony\EasyBatchSymfonyBundle;
use EonX\EasyEncryption\Bridge\Symfony\EasyEncryptionSymfonyBundle;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\MessageBusInterface;

final class KernelStub extends Kernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(EventDispatcherInterface::class, new Definition(\stdClass::class));
        $container->setDefinition(LockServiceInterface::class, new Definition(\stdClass::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(\stdClass::class));
        $container->setDefinition(RandomGeneratorInterface::class, new Definition(\stdClass::class));
        $container->setDefinition(Connection::class, new Definition(\stdClass::class));

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }
    }

    /**
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyEncryptionSymfonyBundle();
        yield new EasyBatchSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
