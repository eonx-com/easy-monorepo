<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Repositories;

use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Tests\AbstractRepositoriesTestCase;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Pagination;
use stdClass;

final class BatchItemRepositoryTest extends AbstractRepositoriesTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testFindForDispatch
     */
    public static function providerTestFindForDispatch(): iterable
    {
        yield 'Fetch only batchItems for batch and no dependency' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem1->setName('right-one');
                $batchItem1->setMetadata(['key' => 'value']);

                $batchItem2 = $factory->create('another-batch-id', new stdClass());
                $batchItem3 = $factory->create('batch-id', new stdClass())
                    ->setDependsOnName('dependency');

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (LengthAwarePaginatorInterface $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals('right-one', $paginator->getItems()[0]->getName());
            },
        ];

        yield 'Fetch only batchItems for batch and given dependency' => [
            static function (BatchItemFactoryInterface $factory, BatchItemRepositoryInterface $repo): void {
                $batchItem1 = $factory->create('batch-id', new stdClass());
                $batchItem1->setName('right-one');
                $batchItem1->setDependsOnName('dependency');

                $batchItem2 = $factory->create('another-batch-id', new stdClass());
                $batchItem3 = $factory->create('batch-id', new stdClass());
                $batchItem3->setName('dependency');

                $repo->save($batchItem1);
                $repo->save($batchItem2);
                $repo->save($batchItem3);
            },
            static function (LengthAwarePaginatorInterface $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals('right-one', $paginator->getItems()[0]->getName());
            },
            'dependency',
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testFindCountsForBatch(): void
    {
        $factory = $this->getBatchItemFactory();
        $repo = $this->getBatchItemRepository($factory);

        $batchItem1 = $factory->create('batch-id', new stdClass());
        $batchItem2 = $factory->create('batch-id', new stdClass());
        $batchItem2->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

        $repo->save($batchItem1);
        $repo->save($batchItem2);

        $counts = $repo->findCountsForBatch('batch-id');

        self::assertEquals(0, $counts->countCancelled());
        self::assertEquals(0, $counts->countFailed());
        self::assertEquals(1, $counts->countProcessed());
        self::assertEquals(1, $counts->countSucceeded());
        self::assertEquals(2, $counts->countTotal());
    }

    /**
     * @dataProvider providerTestFindForDispatch
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testFindForDispatch(callable $setup, callable $test, ?string $dependsOnName = null): void
    {
        $factory = $this->getBatchItemFactory();
        $repo = $this->getBatchItemRepository($factory);

        \call_user_func($setup, $factory, $repo);

        $paginator = $repo->paginateItems(new Pagination(1, 15), 'batch-id', $dependsOnName);

        \call_user_func($test, $paginator);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getBatchItemRepository(BatchItemFactoryInterface $batchItemFactory): BatchItemRepositoryInterface
    {
        return new BatchItemRepository(
            $batchItemFactory,
            $this->getIdStrategy(),
            new BatchItemTransformer(new MessageSerializer()),
            $this->getDoctrineDbalConnection(),
            BatchItemRepositoryInterface::DEFAULT_TABLE
        );
    }
}
