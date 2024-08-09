<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Doctrine\Iterator;

use Closure;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;
use EonX\EasyBatch\Tests\Unit\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class BatchItemIteratorTest extends AbstractSymfonyTestCase
{
    private static int $iterateFuncCalls = 0;

    /**
     * @var string[]
     */
    private static array $iteratedItems = [];

    /**
     * @see testIterateThroughItems
     */
    public static function provideIterateThroughItemsData(): iterable
    {
        yield '1 page, no changes during iteration, no reset pagination' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem2 = $factory->create('batch-id', new stdClass());

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
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem2 = $factory->create('batch-id', new stdClass());
                $batchItem3 = $factory->create('batch-id', new stdClass());

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
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem2 = $factory->create('batch-id', new stdClass());

                $repo->save($batchItem1);
                $repo->save($batchItem2);
            },
            static function (): void {
                self::assertIterateFuncCalls(2);
            },
            static function (): void {
                self::$iterateFuncCalls++;
            },
            static fn (BatchItemRepositoryInterface $batchItemRepo): Closure => static function (
                array $batchItems,
            ) use ($batchItemRepo): void {
                foreach ($batchItems as $batchItem) {
                    $batchItem->setStatus(BatchObjectStatus::Succeeded);
                    $batchItemRepo->save($batchItem);
                }
            },
        ];

        yield '2 pages, status changed during iteration, reset pagination, 1 item processed twice' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem1->setName('batchItem1');
                $batchItem2 = $factory->create('batch-id', new stdClass());
                $batchItem2->setName('batchItem2');
                $batchItem3 = $factory->create('batch-id', new stdClass());
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
            static fn (BatchItemRepositoryInterface $batchItemRepo): Closure => static function (
                array $batchItems,
            ) use ($batchItemRepo): void {
                foreach ($batchItems as $batchItem) {
                    if ($batchItem->getName() === 'batchItem1') {
                        $batchItem->setStatus(BatchObjectStatus::Succeeded);
                        $batchItemRepo->save($batchItem);
                    }
                }
            },
        ];
    }

    #[DataProvider('provideIterateThroughItemsData')]
    public function testIterateThroughItems(
        callable $setup,
        callable $assert,
        callable $iterateFunc,
        ?callable $getCurrentPageCallback = null,
        ?string $batchId = null,
        ?int $batchItemPerPage = null,
    ): void {
        $container = $this->getKernel()
            ->getContainer();

        $batchItemFactory = $container->get(BatchItemFactoryInterface::class);
        $batchItemRepo = $container->get(BatchItemRepositoryInterface::class);

        $setup($batchItemFactory, $batchItemRepo);

        $iteratorConfig = (BatchItemIteratorConfig::create($batchId ?? 'batch-id', $iterateFunc))
            ->setBatchItemsPerPage($batchItemPerPage ?? 2)
            ->forDispatch();

        if ($getCurrentPageCallback !== null) {
            $iteratorConfig->setCurrentPageCallback($getCurrentPageCallback($batchItemRepo));
        }

        $container->get(BatchItemIteratorInterface::class)->iterateThroughItems($iteratorConfig);

        \call_user_func($assert);
    }

    private static function assertIterateFuncCalls(int $calls): void
    {
        self::assertSame($calls, self::$iterateFuncCalls);
        self::$iterateFuncCalls = 0;
    }
}
