<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\EloquentPaginator;
use EonX\EasyPagination\Tests\AbstractEloquentTestCase;
use EonX\EasyPagination\Tests\Stubs\Model\Item;
use EonX\EasyPagination\Tests\Stubs\Model\ParentModel;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class EloquentPaginatorTest extends AbstractEloquentTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestPaginator(): iterable
    {
        yield 'Default 0 items' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model): void {
                $this->createItemsTable($model);
            },
            static function (EloquentPaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
            },
        ];

        yield 'High pagination when no items in db' => [
            Pagination::create(10, 15),
            new Item(),
            function (Model $model): void {
                $this->createItemsTable($model);
            },
            static function (EloquentPaginator $paginator): void {
                self::assertEmpty($paginator->getItems());
            },
        ];

        yield 'Default 1 item' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();
            },
            static function (EloquentPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
            },
        ];

        yield '2 items filter 1' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model, EloquentPaginator $paginator): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();
                (new Item(['title' => 'my-title-1']))->save();

                $paginator->setCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder->where('title', 'my-title-1');
                });
            },
            static function (EloquentPaginator $paginator): void {
                self::assertCount(1, $paginator->getItems());
            },
        ];

        yield '1 item select everything by default' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();
            },
            static function (EloquentPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(Item::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select everything explicitly' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model, EloquentPaginator $paginator): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();

                $paginator->setSelect('*');
            },
            static function (EloquentPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(Item::class, $item);
                self::assertEquals(1, $item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item select only title' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model, EloquentPaginator $paginator): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();

                $paginator->setSelect('title');
            },
            static function (EloquentPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(Item::class, $item);
                self::assertNull($item->id);
                self::assertEquals('my-title', $item->title);
            },
        ];

        yield '1 item transform entity to array' => [
            Pagination::create(1, 15),
            new Item(),
            function (Model $model, EloquentPaginator $paginator): void {
                $this->createItemsTable($model);

                (new Item(['title' => 'my-title']))->save();

                $paginator->setTransformer(static function (Item $item): array {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                    ];
                });
            },
            static function (EloquentPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertIsArray($item);
                self::assertEquals(1, $item['id']);
                self::assertEquals('my-title', $item['title']);
            },
        ];

        yield 'Paginate parents of item by title' => [
            Pagination::create(1, 15),
            new ParentModel(),
            function (Model $model, EloquentPaginator $paginator): void {
                $this->createItemsTable($model);
                $this->createParentsTable($model);

                (new Item(['title' => 'my-title']))->save();
                (new ParentModel(['title' => 'my-parent', 'item_id' => 1]))->save();

                $paginator->hasJoinsInQuery();
                $paginator->setCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder->join('items', 'items.title', '=', 'my-title');
                });
                $paginator->setGetItemsCriteria(static function (Builder $queryBuilder): void {
                    $queryBuilder
                        ->join('items', 'items.id', '=', 'parents.item_id')
                        ->with('item');
                });
            },
            static function (EloquentPaginator $paginator): void {
                $item = $paginator->getItems()[0] ?? null;

                self::assertCount(1, $paginator->getItems());
                self::assertInstanceOf(ParentModel::class, $item);
                self::assertInstanceOf(Item::class, $item->item);
                self::assertEquals(1, $item->id);
                self::assertEquals(1, $item->item->id);
                self::assertEquals('my-title', $item->item->title);
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
        Model $model,
        callable $setup,
        callable $assert
    ): void {
        $connectionResolver = new ConnectionResolver([
            'default' => $this->getEloquentConnection(),
        ]);
        $connectionResolver->setDefaultConnection('default');

        Model::setConnectionResolver($connectionResolver);

        $paginator = new EloquentPaginator($pagination, $model);

        $setup($model, $paginator);
        $assert($paginator);
    }
}
