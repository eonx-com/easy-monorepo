<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\DoctrineDbalPaginator;
use EonX\EasyPagination\Tests\AbstractDoctrineDbalTestCase;

final class DoctrineDbalPaginatorTest extends AbstractDoctrineDbalTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestPaginator(): iterable
    {
        yield 'Default 0 items' => [
            Pagination::create(1, 15),
            'items',
            null,
            function (Connection $conn): void {
                $this->createItemsTable($conn);
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
                $this->createItemsTable($conn);
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
                $this->createItemsTable($conn);
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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
                $this->addItemToTable($conn, 'my-title-1');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
                $this->addItemToTable($conn, 'my-title-1');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');
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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');

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
                $this->createItemsTable($conn);
                $this->addItemToTable($conn, 'my-title');

                $paginator->setTransformer(static function (array $item): \stdClass {
                    $obj = new \stdClass();

                    foreach ($item as $key => $value) {
                        $obj->{$key} = $value;
                    }

                    return $obj;
                });
            },
            static function (DoctrineDbalPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(\stdClass::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield 'Paginate parents of item by title' => [
            Pagination::create(1, 15),
            'parents',
            'p',
            function (Connection $conn, DoctrineDbalPaginator $paginator): void {
                $this->createItemsTable($conn);
                $this->createParentsTable($conn);
                $this->addItemToTable($conn, 'my-title');
                $this->addParentToTable($conn, 'my-parent', 1);

                $paginator->hasJoinsInQuery();
                $paginator->setCommonCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('p', 'items', 'i', 'i.title = :title')
                        ->setParameter('title', 'my-title');
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
                self::assertEquals('my-title', $item['title']);
                self::assertEquals('my-parent', $item['parent_title']);
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
