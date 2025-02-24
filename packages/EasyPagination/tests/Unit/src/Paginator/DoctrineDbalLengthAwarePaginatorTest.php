<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\DoctrineDbalLengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use Symfony\Component\Uid\Uuid;

final class DoctrineDbalLengthAwarePaginatorTest extends AbstractDoctrineDbalPaginatorTestCase
{
    use ProphecyTrait;

    /**
     * @see testPaginator
     */
    public static function providePaginatorData(): iterable
    {
        $childItemId = Uuid::v6();

        yield 'Default 0 items' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection): void {
                self::createItemsTable($connection);
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
                self::assertEquals('?page=1&perPage=15', $paginator->getFirstPageUrl());
                self::assertEquals('?page=1&perPage=15', $paginator->getLastPageUrl());
                self::assertEquals('?page=2&perPage=15', $paginator->getNextPageUrl());
                self::assertEquals('?page=1&perPage=15', $paginator->getLastPageUrl());
                self::assertEquals('?page=10&perPage=15', $paginator->getPageUrl(10));
                self::assertEquals([
                    'items' => [],
                    'pagination' => [
                        'page' => 1,
                        'perPage' => 15,
                        'nextPageUrl' => '?page=2&perPage=15',
                        'previousPageUrl' => '?page=1&perPage=15',
                        'firstPageUrl' => '?page=1&perPage=15',
                        'lastPageUrl' => '?page=1&perPage=15',
                        'totalPages' => 1,
                        'hasNextPage' => false,
                        'hasPreviousPage' => false,
                    ],
                ], $paginator->toArray());
            },
        ];

        yield 'Default 0 items with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection): void {
                self::createItemsTable($connection);
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
            },
        ];

        yield 'High pagination when no items in db' => [
            Pagination::create(10, 15),
            'items',
            null,
            function (Connection $connection): void {
                self::createItemsTable($connection);
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
            },
        ];

        yield 'Default 1 item' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield 'Default 1 item with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $paginator->setPrimaryKeyIndex('id');

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield '2 items filter 1' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
                self::addItemToTable($connection, 'my-title-1');

                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->where('title = :title')
                        ->setParameter('title', 'my-title-1');
                });
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield '2 items filter 1 with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
                self::addItemToTable($connection, 'my-title-1');

                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->where('i.title = :title')
                        ->setParameter('title', 'my-title-1');
                });
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield '1 item select everything by default' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select everything by default with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select everything explicitly' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');

                $paginator->setSelect('*');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select everything explicitly with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');

                $paginator->setSelect('*');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select only title' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');

                $paginator->setSelect('title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayNotHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select only title with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');

                $paginator->setSelect('title');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayNotHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item transform array to object' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');

                $paginator->setTransformer(static function (array $item): stdClass {
                    $obj = new stdClass();

                    foreach ($item as $key => $value) {
                        $obj->{$key} = $value;
                    }

                    return $obj;
                });
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertInstanceOf(stdClass::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield 'Paginate children of item by title' => [
            Pagination::create(1, 15),
            'child_items',
            'ci',
            function (Connection $connection, DoctrineDbalLengthAwarePaginator $paginator) use ($childItemId): void {
                self::createItemsTable($connection);
                self::createChildItemsTable($connection);
                self::addItemToTable($connection, 'my-parent');
                self::addChildItemToTable($connection, 'my-child', $childItemId);

                // $paginator->hasJoinsInQuery();
                $paginator->setPrimaryKeyIndex('id');
                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('ci', 'items', 'i', 'i.title = :title')
                        ->setParameter('title', 'my-parent');

                    $queryBuilder->addSelect('i.*');
                });
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator) use ($childItemId): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals($childItemId, $item['item_id']);
                self::assertEquals('my-parent', $item['title']);
                self::assertEquals('my-child', $item['child_title']);
            },
        ];

        yield '2 items, 1 perPage' => [
            Pagination::create(1, 1),
            'items',
            null,
            function (Connection $connection): void {
                self::createItemsTable($connection);
                self::addItemToTable($connection, 'my-title');
                self::addItemToTable($connection, 'my-title-1');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(2, $paginator->getTotalItems());
            },
        ];
    }

    /**
     * @see testPaginatorGetTotalItems
     */
    public static function provideRowsCount(): iterable
    {
        yield 'Without precise calculation' => [
            'approximateRowsCount' => 10000,
            'preciseRowsCount' => 10,
            'expectedRowsCount' => 10000,
        ];

        yield 'With precise calculation' => [
            'approximateRowsCount' => 99,
            'preciseRowsCount' => 97,
            'expectedRowsCount' => 97,
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    #[DataProvider('providePaginatorData')]
    public function testPaginator(
        PaginationInterface $pagination,
        string $from,
        ?string $fromAlias,
        callable $setup,
        callable $assert,
    ): void {
        $connection = $this->getDoctrineDbalConnection();
        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $connection, $from, $fromAlias);
        $paginator->setLargeDatasetEnabled();

        $setup($connection, $paginator);
        $assert($paginator);
    }

    #[DataProvider('provideRowsCount')]
    public function testPaginatorGetTotalItems(
        int $approximateRowsCount,
        int $preciseRowsCount,
        int $expectedRowsCount,
    ): void {
        $connection = $this->prophesize(Connection::class);
        $connection
            ->createQueryBuilder()
            ->willReturn($this->getDoctrineDbalConnection()->createQueryBuilder());
        $connection
            ->getDatabasePlatform()
            ->willReturn(new SqlitePlatform());
        $result = $this->prophesize(Result::class);
        $connection
            ->executeQuery(Argument::any(), [], [])
            ->willReturn($result->reveal());
        $result
            ->fetchAssociative()
            ->willReturn([
                'QUERY PLAN' => \sprintf('rows=%d', $approximateRowsCount),
            ]);
        $connection
            ->fetchAllAssociative('SELECT COUNT(DISTINCT i.id) as _count_i FROM items i', [], [])
            ->willReturn([['_count_i' => $preciseRowsCount]]);
        $paginator = new DoctrineDbalLengthAwarePaginator(
            Pagination::create(1, 1),
            $connection->reveal(),
            'items',
            'i'
        );
        self::createItemsTable($this->getDoctrineDbalConnection());
        $paginator->setLargeDatasetEnabled();
        $paginator->setLargeDatasetPaginationPreciseResultsLimit(100);

        self::assertEquals($expectedRowsCount, $paginator->getTotalItems());
    }
}
