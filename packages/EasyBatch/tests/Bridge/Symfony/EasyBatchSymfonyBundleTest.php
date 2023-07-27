<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\Batch;
use stdClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EasyBatchSymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testCoreLogic
     */
    public static function providerTestCoreLogic(): iterable
    {
        yield 'Manually approve single item within nested batch' => [
            static function (EasyBatchTestContext $context): void {
                $disbursementTransferItem = $context->getBatchItemFactory()
                    ->create('dt-batch-id', new stdClass());
                $disbursementTransferItem->setId('to-approve');
                $disbursementTransferItem->setApprovalRequired(true);
                $disbursementTransferItem->setStatus(BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL);

                $disbursementTransferParentItem = $context->getBatchItemFactory()
                    ->create('dj-batch-id', new stdClass());
                $disbursementTransferParentItem->setStatus(BatchItemInterface::STATUS_BATCH_PENDING_APPROVAL);
                $disbursementTransferParentItem->setType(BatchItemInterface::TYPE_NESTED_BATCH);
                $disbursementTransferParentItem->setId('dt-parent-batch-item');

                $disbursementTransferBatch = new Batch();
                $disbursementTransferBatch->setId('dt-batch-id');
                $disbursementTransferBatch->setTotal(1);
                $disbursementTransferBatch->setStatus(BatchObjectInterface::STATUS_PROCESSING);
                $disbursementTransferBatch->setParentBatchItemId('dt-parent-batch-item');

                $disbursementJobBatch = new Batch();
                $disbursementJobBatch->setId('dj-batch-id');
                $disbursementJobBatch->setTotal(1);
                $disbursementJobBatch->setStatus(BatchObjectInterface::STATUS_PENDING);
                $disbursementJobBatch->setType('disbursement');

                $context->getBatchItemRepository()
                    ->save($disbursementTransferItem);
                $context->getBatchItemRepository()
                    ->save($disbursementTransferParentItem);
                $context->getBatchRepository()
                    ->save($disbursementTransferBatch);
                $context->getBatchRepository()
                    ->save($disbursementJobBatch);
            },
            static function (EasyBatchTestContext $context): void {
                $disbursementTransferItem = $context->getBatchItemRepository()
                    ->findOrFail('to-approve');

                $context->getBatchObjectManager()
                    ->approve($disbursementTransferItem);
            },
            static function (EasyBatchTestContext $context): void {
                // All objects should be succeeded
                $batches = $context->getConnection()
                    ->fetchAllAssociative('select * from easy_batches');
                $items = $context->getConnection()
                    ->fetchAllAssociative('select * from easy_batch_items');

                foreach ($batches as $batch) {
                    self::assertEquals(BatchObjectInterface::STATUS_SUCCEEDED, $batch['status']);
                }

                foreach ($items as $item) {
                    self::assertEquals(BatchObjectInterface::STATUS_SUCCEEDED, $item['status']);
                }
            },
        ];
    }

    /**
     * @see testRestoreBatchState
     */
    public static function providerTestRestoreBatchState(): iterable
    {
        yield 'Simple batch update (batch processing)' => [
            static function (
                BatchItemFactoryInterface $batchItemFactory,
                BatchItemRepositoryInterface $batchItemRepo,
                BatchRepositoryInterface $batchRepo,
            ): void {
                // Batch items
                $batchItemCreated = $batchItemFactory->create('batch-id', new stdClass());

                $batchItemCompleted = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCompleted->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCancelled->setStatus(BatchObjectInterface::STATUS_CANCELLED);

                $batchItemRepo->save($batchItemCreated);
                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // Batch
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
                BatchRepositoryInterface $batchRepo,
            ): void {
                // Batch items
                $batchItemCompleted = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCompleted->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCancelled->setStatus(BatchObjectInterface::STATUS_CANCELLED);

                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // Batch
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

    /**
     * @dataProvider providerTestCoreLogic
     */
    public function testCoreLogic(callable $setup, callable $runTest, callable $assert): void
    {
        $context = new EasyBatchTestContext($this->getContainer());

        \call_user_func($setup, $context);
        \call_user_func($runTest, $context);
        \call_user_func($assert, $context);
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

    public function testSanity(): void
    {
        $container = $this->getContainer();

        self::assertInstanceOf(BatchObjectManagerInterface::class, $container->get(BatchObjectManagerInterface::class));
    }
}
