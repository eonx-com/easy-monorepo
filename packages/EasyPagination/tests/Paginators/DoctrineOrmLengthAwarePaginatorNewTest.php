<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginatorNew;
use EonX\EasyPagination\Tests\AbstractDoctrineOrmTestCase;
use EonX\EasyPagination\Tests\Stubs\Entity\Item;
use EonX\EasyPagination\Tests\Stubs\Entity\ParentEntity;

final class DoctrineOrmLengthAwarePaginatorNewTest extends AbstractDoctrineOrmTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestPaginator(): iterable
    {
        yield 'Default 0 items' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager): void {
                $this->createItemsTable($manager);
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
            },
        ];

        yield 'High pagination when no items in db' => [
            Pagination::create(10, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager): void {
                $this->createItemsTable($manager);
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
            },
        ];

        yield 'Default 1 item' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield '2 items filter 1' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager, DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');
                $this->addItemToTable($manager, 'my-title-1');

                $paginator->setCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->where('i.title = :title')
                        ->setParameter('title', 'my-title-1');
                });
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
            },
        ];

        yield '1 item select everything by default' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertInstanceOf(Item::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select everything explicitly' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager, DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');

                $paginator->setSelect('*');
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertInstanceOf(Item::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select only title' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager, DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');

                $paginator->setSelect('i.title');
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertArrayNotHasKey('id', $item);
                self::assertArrayHasKey('title', $item);
            },
        ];

        yield '1 item transform entity to array' => [
            Pagination::create(1, 15),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager, DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');

                $paginator->setTransformer(static function (Item $item): array {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                    ];
                });
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals('my-title', $item['title']);
            },
        ];

        yield 'Paginate parents of item by title' => [
            Pagination::create(1, 15),
            ParentEntity::class,
            'p',
            null,
            function (EntityManagerInterface $manager, DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $this->createItemsTable($manager);
                $this->createParentsTable($manager);
                $item = $this->addItemToTable($manager, 'my-title');
                $this->addParentToTable($manager, 'my-parent', $item);

                $paginator->hasJoinsInQuery();
                $paginator->setCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('p.item', 'i', 'WITH', 'i.title = :title')
                        ->setParameter('title', 'my-title');
                });
                $paginator->setGetItemsCriteria(static function (QueryBuilder $queryBuilder): void {
                    $queryBuilder
                        ->join('p.item', 'i')
                        ->addSelect('i');
                });
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertInstanceOf(ParentEntity::class, $item);
                self::assertInstanceOf(Item::class, $item->item);
                self::assertEquals(1, $item->id);
                self::assertEquals(1, $item->item->id);
                self::assertEquals('my-title', $item->item->title);
                self::assertEquals('my-parent', $item->title);
            },
        ];

        yield '2 items, 1 perPage' => [
            Pagination::create(1, 1),
            Item::class,
            'i',
            null,
            function (EntityManagerInterface $manager): void {
                $this->createItemsTable($manager);
                $this->addItemToTable($manager, 'my-title');
                $this->addItemToTable($manager, 'my-title-1');
            },
            static function (DoctrineOrmLengthAwarePaginatorNew $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(2, $paginator->getTotalItems());
            },
        ];
    }

    /**
     * @dataProvider providerTestPaginator
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function testPaginator(
        PaginationInterface $pagination,
        string $from,
        string $fromAlias,
        ?string $indexBy,
        callable $setup,
        callable $assert
    ): void {
        $entityManager = $this->getEntityManager();
        $paginator = new DoctrineOrmLengthAwarePaginatorNew($pagination, $entityManager, $from, $fromAlias, $indexBy);

        $setup($entityManager, $paginator);
        $assert($paginator);
    }
}
