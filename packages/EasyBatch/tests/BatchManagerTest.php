<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests;

use EonX\EasyBatch\BatchManager;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyBatch\Repositories\BatchRepository;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Tests\Stubs\AsyncDispatcherStub;
use EonX\EasyBatch\Tests\Stubs\EventDispatcherStub;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyBatch\Transformers\BatchTransformer;

final class BatchManagerTest extends AbstractRepositoriesTestCase
{
    private static $iterateFuncCalls = 0;

    /**
     * @var string[]
     */
    private static $iteratedItems = [];

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepo;

    /**
     * @return iterable<mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function providerTestIterateThroughItems(): iterable
    {
        yield '1 page, no changes during iteration, no reset pagination' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new \stdClass());
                $batchItem2 = $factory->create('batch-id', new \stdClass());

                $repo->save($batchItem1);
                $repo->save($batchItem2);
            },
            static function (): void {
                self::assertIterateFuncCalls(2);
            },
            static function (): void {
                self::$iterateFuncCalls++;
            },
        ];

        yield '2 pages, no changes during iteration, no reset pagination' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new \stdClass());
                $batchItem2 = $factory->create('batch-id', new \stdClass());
                $batchItem3 = $factory->create('batch-id', new \stdClass());

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (): void {
                self::assertIterateFuncCalls(3);
            },
            static function (): void {
                self::$iterateFuncCalls++;
            },
        ];

        yield '1 page, status changed during iteration, no reset pagination' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new \stdClass());
                $batchItem2 = $factory->create('batch-id', new \stdClass());

                $repo->save($batchItem1);
                $repo->save($batchItem2);
            },
            static function (): void {
                self::assertIterateFuncCalls(2);
            },
            static function (BatchItemInterface $batchItem, BatchItemRepositoryInterface $batchItemRepo) : void {
                $batchItem->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);
                $batchItemRepo->save($batchItem);

                self::$iterateFuncCalls++;
            },
        ];

        yield '2 pages, status changed during iteration, reset pagination, 1 item processed twice' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new \stdClass());
                $batchItem1->setName('batchItem1');
                $batchItem2 = $factory->create('batch-id', new \stdClass());
                $batchItem2->setName('batchItem2');
                $batchItem3 = $factory->create('batch-id', new \stdClass());
                $batchItem3->setName('batchItem3');

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (): void {
                self::assertIterateFuncCalls(4);
                self::assertEquals([
                    'batchItem1',
                    'batchItem2',
                    'batchItem2',
                    'batchItem3',
                ], self::$iteratedItems);
            },
            static function (BatchItemInterface $batchItem, BatchItemRepositoryInterface $batchItemRepo) : void {
                if ($batchItem->getName() === 'batchItem1') {
                    $batchItem->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);
                    $batchItemRepo->save($batchItem);
                }

                self::$iteratedItems[] = $batchItem->getName();
                self::$iterateFuncCalls++;
            },
        ];
    }

    /**
     * @dataProvider providerTestIterateThroughItems
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testIterateThroughItems(
        callable $setup,
        callable $assert,
        callable $iterateFunc,
        ?string $batchId = null,
        ?int $batchItemPerPage = null
    ): void {
        $batchRepo = new BatchRepository(
            $this->getBatchFactory(),
            $this->getIdStrategy(),
            new BatchTransformer(new MessageSerializer()),
            $this->getDoctrineDbalConnection(),
            BatchRepositoryInterface::DEFAULT_TABLE
        );
        $batchItemFactory = $this->getBatchItemFactory();
        $batchItemRepo = $this->getBatchItemRepository();

        $batchManager = new BatchManager(
            new AsyncDispatcherStub(),
            $batchRepo,
            $batchItemFactory,
            $batchItemRepo,
            new EventDispatcherStub(),
            $batchItemPerPage ?? 2
        );

        \call_user_func($setup, $batchItemFactory, $batchItemRepo);

        $batchManager->iterateThroughItems($batchId ?? 'batch-id', null, $iterateFunc);

        \call_user_func($assert);
    }

    private static function assertIterateFuncCalls(int $calls): void
    {
        self::assertEquals($calls, self::$iterateFuncCalls);
        self::$iterateFuncCalls = 0;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getBatchItemRepository(): BatchItemRepositoryInterface
    {
        return $this->batchItemRepo = $this->batchItemRepo ?? new BatchItemRepository(
            $this->getBatchItemFactory(),
            $this->getIdStrategy(),
            new BatchItemTransformer(new MessageSerializer()),
            $this->getDoctrineDbalConnection(),
            BatchItemRepositoryInterface::DEFAULT_TABLE
        );
    }
}
