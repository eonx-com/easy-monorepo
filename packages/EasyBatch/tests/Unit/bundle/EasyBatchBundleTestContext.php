<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Bundle;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Tests\Stub\EventDispatcher\SymfonyEventDispatcherStub;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EasyBatchBundleTestContext
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function getBatchItemFactory(): BatchItemFactoryInterface
    {
        return $this->container->get(BatchItemFactoryInterface::class);
    }

    public function getBatchItemRepository(): BatchItemRepositoryInterface
    {
        return $this->container->get(BatchItemRepositoryInterface::class);
    }

    public function getBatchObjectManager(): BatchObjectManagerInterface
    {
        return $this->container->get(BatchObjectManagerInterface::class);
    }

    public function getBatchRepository(): BatchRepositoryInterface
    {
        return $this->container->get(BatchRepositoryInterface::class);
    }

    public function getConnection(): Connection
    {
        return $this->container->get(Connection::class);
    }

    public function getEventDispatcher(): SymfonyEventDispatcherStub
    {
        /** @var \EonX\EasyBatch\Tests\Stub\EventDispatcher\SymfonyEventDispatcherStub $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        return $eventDispatcher;
    }
}
