<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\EloquentLengthAwarePaginator;
use EonX\EasyPagination\Tests\AbstractEloquentTestCase;
use EonX\EasyPagination\Tests\Stubs\Model\ChildItemModel;
use EonX\EasyPagination\Tests\Stubs\Model\ItemModel;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class EloquentLengthAwarePaginatorTest extends AbstractEloquentTestCase
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
            new ItemModel(),
            function (Model $model): void {
                self::createItemsTable($model);
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
            },
        ];

        yield 'High pagination when no items in db' => [
            Pagination::create(10, 15),
            new ItemModel(),
            function (Model $model): void {
                self::createItemsTable($model);
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
                self::assertEquals(0, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
            },
        ];

        yield 'Default 1 item' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
            },
        ];

        yield '2 items filter 1' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model, EloquentLengthAwarePaginator $paginator): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();
                (new ItemModel(['title' => 'my-title-1']))->save();

                $paginator->setFilterCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder->where('title', 'my-title-1');
                });
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
            },
        ];

        yield '1 item select everything by default' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(ItemModel::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select everything explicitly' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model, EloquentLengthAwarePaginator $paginator): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();

                $paginator->setSelect('*');
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(ItemModel::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select only title' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model, EloquentLengthAwarePaginator $paginator): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();

                $paginator->setSelect('title');
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(ItemModel::class, $item);
                self::assertNull($item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item transform entity to array' => [
            Pagination::create(1, 15),
            new ItemModel(),
            function (Model $model, EloquentLengthAwarePaginator $paginator): void {
                self::createItemsTable($model);

                (new ItemModel(['title' => 'my-title']))->save();

                $paginator->setTransformer(static fn (ItemModel $item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                ]);
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals('my-title', $item['title']);
            },
        ];

        yield 'Paginate children of item by title' => [
            Pagination::create(1, 15),
            new ChildItemModel(),
            function (Model $model, EloquentLengthAwarePaginator $paginator): void {
                self::createItemsTable($model);
                self::createChildItemsTable($model);

                (new ItemModel(['title' => 'my-parent']))->save();
                (new ChildItemModel([
                    'child_title' => 'my-child',
                    'item_id' => 1,
                ]))->save();

                $paginator->hasJoinsInQuery();
                $paginator->setCommonCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder->join('items', 'items.title', '=', 'my-parent');
                });
                $paginator->setGetItemsCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder->with('item');
                });
            },
            static function (EloquentLengthAwarePaginator $paginator): void {
                $childItem = $paginator->getItems()[0] ?? null;

                self::assertEquals(1, $paginator->getTotalItems());
                self::assertEquals(1, $paginator->getTotalPages());
                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(ChildItemModel::class, $childItem);
                self::assertInstanceOf(ItemModel::class, $childItem->item);
                self::assertEquals(1, $childItem->id);
                self::assertEquals(1, $childItem->item->id);
                self::assertEquals('my-parent', $childItem->item->title);
                self::assertEquals('my-child', $childItem->child_title);
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
        Model $model,
        callable $setup,
        callable $assert,
    ): void {
        $connectionResolver = new ConnectionResolver([
            'default' => $this->getEloquentConnection(),
        ]);
        $connectionResolver->setDefaultConnection('default');

        Model::setConnectionResolver($connectionResolver);

        $paginator = new EloquentLengthAwarePaginator($pagination, $model);

        $setup($model, $paginator);
        $assert($paginator);
    }
}
