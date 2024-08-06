<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Bundle;

use EonX\EasyBatch\Common\Enum\BatchItemType;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Event\BatchCompletedEvent;
use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Tests\Unit\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EasyBatchBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testCoreLogic
     */
    public static function provideCoreLogicData(): iterable
    {
        yield 'Manually approve single item within nested batch' => [
            static function (EasyBatchBundleTestContext $context): void {
                $disbursementTransferItem = $context->getBatchItemFactory()
                    ->create('dt-batch-id', new stdClass());
                $disbursementTransferItem->setId('to-approve');
                $disbursementTransferItem->setApprovalRequired(true);
                $disbursementTransferItem->setStatus(BatchObjectStatus::SucceededPendingApproval);

                $disbursementTransferParentItem = $context->getBatchItemFactory()
                    ->create('dj-batch-id', new stdClass());
                $disbursementTransferParentItem->setStatus(BatchObjectStatus::BatchPendingApproval);
                $disbursementTransferParentItem->setType(BatchItemType::NestedBatch->value);
                $disbursementTransferParentItem->setId('dt-parent-batch-item');

                $disbursementTransferBatch = new Batch();
                $disbursementTransferBatch->setId('dt-batch-id');
                $disbursementTransferBatch->setTotal(1);
                $disbursementTransferBatch->setStatus(BatchObjectStatus::Processing);
                $disbursementTransferBatch->setParentBatchItemId('dt-parent-batch-item');

                $disbursementJobBatch = new Batch();
                $disbursementJobBatch->setId('dj-batch-id');
                $disbursementJobBatch->setTotal(1);
                $disbursementJobBatch->setStatus(BatchObjectStatus::Pending);
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
            static function (EasyBatchBundleTestContext $context): void {
                $disbursementTransferItem = $context->getBatchItemRepository()
                    ->findOrFail('to-approve');

                $context->getBatchObjectManager()
                    ->approve($disbursementTransferItem);
            },
            static function (EasyBatchBundleTestContext $context): void {
                // All objects should be succeeded
                $batches = $context->getConnection()
                    ->fetchAllAssociative('select * from easy_batches');
                $items = $context->getConnection()
                    ->fetchAllAssociative('select * from easy_batch_items');

                foreach ($batches as $batch) {
                    self::assertEquals(BatchObjectStatus::Succeeded->value, $batch['status']);
                }

                foreach ($items as $item) {
                    self::assertEquals(BatchObjectStatus::Succeeded->value, $item['status']);
                }
            },
        ];
    }

    /**
     * @see testRestoreBatchState
     */
    public static function provideRestoreBatchStateData(): iterable
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
                $batchItemCompleted->setStatus(BatchObjectStatus::Succeeded);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCancelled->setStatus(BatchObjectStatus::Cancelled);

                $batchItemRepo->save($batchItemCreated);
                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // Batch
                $batch = new Batch();
                $batch->setId('batch-id');

                $batchRepo->save($batch);
            },
            static function (BatchInterface $batch, array $events): void {
                self::assertEquals(BatchObjectStatus::Processing, $batch->getStatus());
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
                $batchItemCompleted->setStatus(BatchObjectStatus::Succeeded);

                $batchItemCancelled = $batchItemFactory->create('batch-id', new stdClass());
                $batchItemCancelled->setStatus(BatchObjectStatus::Cancelled);

                $batchItemRepo->save($batchItemCompleted);
                $batchItemRepo->save($batchItemCancelled);

                // Batch
                $batch = new Batch();
                $batch->setId('batch-id');

                $batchRepo->save($batch);
            },
            static function (BatchInterface $batch, array $events): void {
                self::assertEquals(BatchObjectStatus::Failed, $batch->getStatus());
                self::assertInstanceOf(BatchCompletedEvent::class, \end($events));
            },
        ];
    }

    #[DataProvider('provideCoreLogicData')]
    public function testCoreLogic(callable $setup, callable $runTest, callable $assert): void
    {
        $context = new EasyBatchBundleTestContext($this->getContainer());

        \call_user_func($setup, $context);
        \call_user_func($runTest, $context);
        \call_user_func($assert, $context);
    }

    #[DataProvider('provideRestoreBatchStateData')]
    public function testRestoreBatchState(callable $setupFunc, callable $assert, ?string $batchId = null): void
    {
        $container = $this->getContainer();
        $batchItemFactory = $container->get(BatchItemFactoryInterface::class);
        $batchItemRepo = $container->get(BatchItemRepositoryInterface::class);
        $batchRepo = $container->get(BatchRepositoryInterface::class);
        $batchObjectManager = $container->get(BatchObjectManagerInterface::class);
        /** @var \EonX\EasyBatch\Tests\Stub\EventDispatcher\SymfonyEventDispatcherStub $eventDispatcher */
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
