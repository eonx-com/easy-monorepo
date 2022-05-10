<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Iterator;

use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Iterator\BatchItemIterator;
use EonX\EasyBatch\Iterator\IteratorConfig;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyBatch\Transformers\BatchItemTransformer;

final class BatchItemIteratorTest extends AbstractSymfonyTestCase
{
    private static int $iterateFuncCalls = 0;

    /**
     * @var string[]
     */
    private static array $iteratedItems = [];

    private ?BatchItemRepositoryInterface $batchItemRepo = null;

    /**
     * @return iterable<mixed>
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
            static function (): void {
                self::$iterateFuncCalls++;
            },
            static function (BatchItemRepositoryInterface $batchItemRepo): \Closure {
                return static function (array $batchItems) use ($batchItemRepo): void {
                    foreach ($batchItems as $batchItem) {
                        $batchItem->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);
                        $batchItemRepo->save($batchItem);
                    }
                };
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
            static function (BatchItemInterface $batchItem): void {
                self::$iteratedItems[] = (string)$batchItem->getName();
                self::$iterateFuncCalls++;
            },
            static function (BatchItemRepositoryInterface $batchItemRepo): \Closure {
                return static function (array $batchItems) use ($batchItemRepo): void {
                    foreach ($batchItems as $batchItem) {
                        if ($batchItem->getName() === 'batchItem1') {
                            $batchItem->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);
                            $batchItemRepo->save($batchItem);
                        }
                    }
                };
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
        ?callable $getCurrentPageCallback = null,
        ?string $batchId = null,
        ?int $batchItemPerPage = null
    ): void {
        $container = $this->getKernel()
            ->getContainer();

        $batchItemFactory = $container->get(BatchItemFactoryInterface::class);
        $batchItemRepo = $container->get(BatchItemRepositoryInterface::class);

        \call_user_func($setup, $batchItemFactory, $batchItemRepo);

        $iteratorConfig = (IteratorConfig::create($batchId ?? 'batch-id', $iterateFunc))
            ->setBatchItemsPerPage($batchItemPerPage ?? 2)
            ->forDispatch();

        if ($getCurrentPageCallback) {
            $iteratorConfig->setCurrentPageCallback($getCurrentPageCallback($batchItemRepo));
        }

        $container->get(BatchItemIterator::class)->iterateThroughItems($iteratorConfig);

        \call_user_func($assert);
    }

    private static function assertIterateFuncCalls(int $calls): void
    {
        self::assertEquals($calls, self::$iterateFuncCalls);
        self::$iterateFuncCalls = 0;
    }
}
