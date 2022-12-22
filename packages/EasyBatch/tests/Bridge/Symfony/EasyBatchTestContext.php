<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\EventDispatcher\EventDispatcherStub;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EasyBatchTestContext
{
    public function __construct(private readonly ContainerInterface $container)
    {
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

    public function getEventDispatcher(): EventDispatcherStub
    {
        /** @var \EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\EventDispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

        return $eventDispatcher;
    }
}
