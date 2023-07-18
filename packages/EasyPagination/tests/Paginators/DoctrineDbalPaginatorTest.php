<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\DoctrineDbalPaginator;
use EonX\EasyPagination\Tests\AbstractDoctrineDbalTestCase;
use stdClass;

final class DoctrineDbalPaginatorTest extends AbstractDoctrineDbalTestCase
{
    /**
     * @return iterable<mixed>
     *
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
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
            },
        ];

        yield 'Default 0 items with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $conn): void {
                self::createItemsTable($conn);
            },
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
            },
        ];

        yield 'High pagination when no items in db' => [
            Pagination::create(10, 15),
            'items',
            null,
            function (Connection $conn): void {
                self::createItemsTable($conn);
            },
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
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
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
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
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
            },
        ];

        yield '2 items filter 1' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
                self::addItemToTable($conn, 'my-title-1');

                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->where('title = :title')
                        ->setParameter('title', 'my-title-1');
                });
            },
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
            },
        ];

        yield '2 items filter 1 with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');
                self::addItemToTable($conn, 'my-title-1');

                $paginator->setFilterCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->where('i.title = :title')
                        ->setParameter('title', 'my-title-1');
                });
            },
            static function (DoctrineDbalPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
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
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
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
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select everything explicitly' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

                $paginator->setSelect('*');
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select everything explicitly with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

                $paginator->setSelect('*');
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertArrayHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select only title' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

                $paginator->setSelect('title');
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertArrayNotHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item select only title with fromAlias' => [
            Pagination::create(1, 15),
            'items',
            'i',
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::addItemToTable($conn, 'my-title');

                $paginator->setSelect('title');
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertArrayNotHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item transform array to object' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
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
            static function (DoctrineDbalPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(stdClass::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield 'Paginate children of item by title' => [
            Pagination::create(1, 15),
            'child_items',
            'ci',
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                self::createItemsTable($conn);
                self::createChildItemsTable($conn);
                self::addItemToTable($conn, 'my-parent');
                self::addChildItemToTable($conn, 'my-child', 1);

                $paginator->hasJoinsInQuery();
                $paginator->setCommonCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('ci', 'items', 'i', 'i.title = :title')
                        ->setParameter('title', 'my-parent');
                });
                $paginator->setGetItemsCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder->addSelect('i.*');
                });
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = (array)($paginator->getItems()[0] ?? []);

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals(1, $item['item_id']);
                self::assertEquals('my-parent', $item['title']);
                self::assertEquals('my-child', $item['child_title']);
            },
        ];
    }

    /**
     * @dataProvider providerTestPaginator
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testPaginator(
        PaginationInterface $pagination,
        string $from,
        ?string $fromAlias,
        callable $setup,
        callable $assert,
    ): void {
        $conn = $this->getDoctrineDbalConnection();
        $paginator = new DoctrineDbalPaginator($pagination, $conn, $from, $fromAlias);

        $setup($conn, $paginator);
        $assert($paginator);
    }
}
