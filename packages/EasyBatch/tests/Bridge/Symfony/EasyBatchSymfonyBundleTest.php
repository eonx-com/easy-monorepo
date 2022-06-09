<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\Batch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EasyBatchSymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestRestoreBatchState(): iterable
    {
        yield 'Simple batch update (batch processing)' => [
            static function (
                BatchItemFactoryInterface $batchItemFactory,
                BatchItemRepositoryInterface $batchItemRepo,
                BatchRepositoryInterface $batchRepo
            ): void {
                // batch items
                $batchItemCreated = $batchItemFactory->create('batch-id', new \stdClass());

                $batchItemCompleted = $batchItemFactory->create('batch-id', new \stdClass());
                $batchItemCompleted->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new \stdClass());
                $batchItemCancelled->setStatus(BatchObjectInterface::STATUS_CANCELLED);

                $batchItemRepo->save($batchItemCreated);
                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // batch
                $batch = new Batch();
                $batch->setId('batch-id');

                $batchRepo->save($batch);
            },
            static function (BatchInterface $batch, array $events): void {
                self::assertEquals(BatchObjectInterface::STATUS_PROCESSING, $batch->getStatus());
                self::assertCount(5, $events);
            },
        ];

        yield 'Simple batch update (batch failed)' => [
            static function (
                BatchItemFactoryInterface $batchItemFactory,
                BatchItemRepositoryInterface $batchItemRepo,
                BatchRepositoryInterface $batchRepo
            ): void {
                // batch items
                $batchItemCompleted = $batchItemFactory->create('batch-id', new \stdClass());
                $batchItemCompleted->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new \stdClass());
                $batchItemCancelled->setStatus(BatchObjectInterface::STATUS_CANCELLED);

                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // batch
                $batch = new Batch();
                $batch->setId('batch-id');

                $batchRepo->save($batch);
            },
            static function (BatchInterface $batch, array $events): void {
                self::assertEquals(BatchObjectInterface::STATUS_FAILED, $batch->getStatus());
                self::assertInstanceOf(BatchCompletedEvent::class, \end($events));
            },
        ];
    }

    public function testSanity(): void
    {
        $container = $this->getContainer();

        self::assertInstanceOf(BatchObjectManagerInterface::class, $container->get(BatchObjectManagerInterface::class));
    }

    /**
     * @dataProvider providerTestRestoreBatchState
     */
    public function testRestoreBatchState(callable $setupFunc, callable $assert, ?string $batchId = null): void
    {
        $container = $this->getContainer();
        $batchItemFactory = $container->get(BatchItemFactoryInterface::class);
        $batchItemRepo = $container->get(BatchItemRepositoryInterface::class);
        $batchRepo = $container->get(BatchRepositoryInterface::class);
        $batchObjectManager = $container->get(BatchObjectManagerInterface::class);
        /** @var \EonX\EasyBatch\Tests\Bridge\Symfony\Stubs\SymfonyEventDispatcherStub $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        \call_user_func($setupFunc, $batchItemFactory, $batchItemRepo, $batchRepo);

        $freshBatch = $batchObjectManager->restoreBatchState($batchId ?? 'batch-id');
        $events = $eventDispatcher->getDispatchedEvents();

        \call_user_func($assert, $freshBatch, $events);
    }
}
