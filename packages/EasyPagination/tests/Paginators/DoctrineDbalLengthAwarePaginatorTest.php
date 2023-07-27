<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;
use EonX\EasyPagination\Tests\AbstractDoctrineDbalTestCase;
use stdClass;

final class DoctrineDbalLengthAwarePaginatorTest extends AbstractDoctrineDbalTestCase
{
    /**
     * @see testPaginator
     */
    public static function providerTestPaginator(): iterable
    {
        yield 'Default 0 items' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn): void {
                self::createItemsTable($conn);
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
            function (Connection $conn): void {
                self::createItemsTable($conn);
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
            function (Connection $conn): void {
                self::createItemsTable($conn);
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
            function (Connection $conn): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
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
            function (Connection $conn): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
                self::addItemToTable($conn, 'my-title-1');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
                self::addItemToTable($conn, 'my-title-1');

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
            function (Connection $conn): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
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
            function (Connection $conn): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

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
            function (Connection $conn, DoctrineDbalLengthAwarePaginator $paginator): void {
                self::createItemsTable($conn);
                self::createChildItemsTable($conn);
                self::addItemToTable($conn, 'my-parent');
                self::addChildItemToTable($conn, 'my-child', 1);

                // $paginator->hasJoinsInQuery();
                $paginator->setPrimaryKeyIndex('id');
                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('ci', 'items', 'i', 'i.title = :title')
                        ->setParameter('title', 'my-parent');

                    $queryBuilder->addSelect('i.*');
                });
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals(1, $item['item_id']);
                self::assertEquals('my-parent', $item['title']);
                self::assertEquals('my-child', $item['child_title']);
            },
        ];

        yield '2 items, 1 perPage' => [
            Pagination::create(1, 1),
            'items',
            null,
            function (Connection $conn): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
                self::addItemToTable($conn, 'my-title-1');
            },
            static function (DoctrineDbalLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(2, $paginator->getTotalItems());
            },
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @dataProvider providerTestPaginator
     */
    public function testPaginator(
        PaginationInterface $pagination,
        string $from,
        ?string $fromAlias,
        callable $setup,
        callable $assert,
    ): void {
        $conn = $this->getDoctrineDbalConnection();
        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $conn, $from, $fromAlias);

        $setup($conn, $paginator);
        $assert($paginator);
    }
}
