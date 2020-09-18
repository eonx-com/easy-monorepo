<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Paginators\EloquentLengthAwarePaginator;
use EonX\EasyPagination\Tests\AbstractWithMockTestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

final class EloquentLengthAwarePaginatorTest extends AbstractWithMockTestCase
{
    public function testGetItems(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $this->mock(Builder::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('count')
                ->atLeast()
                ->once()
                ->with('*')
                ->andReturn(2);

            $mock->shouldReceive('forPage')
                ->atLeast()
                ->once()
                ->with(1, 15)
                ->andReturnSelf();

            $mock->shouldReceive('get')
                ->atLeast()
                ->once()
                ->with(['*'])
                ->andReturn(new Collection([
                    [
                        'id' => 1,
                        'name' => 'Name One',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Name Two',
                    ],
                ]));
        });

        $paginator = new EloquentLengthAwarePaginator($this->mockModel($builder), new StartSizeData(1, 15));

        self::assertCount(2, $paginator->getItems());
        self::assertEquals(2, $paginator->getTotalItems());
    }

    public function testGetItemsWithCriteria(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $this->mock(Builder::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('count')
                ->atLeast()
                ->once()
                ->with('*')
                ->andReturn(1);

            $mock->shouldReceive('where')
                ->atLeast()
                ->once()
                ->with('id', '=', 2)
                ->andReturnSelf();

            $mock->shouldReceive('forPage')
                ->atLeast()
                ->once()
                ->with(1, 15)
                ->andReturnSelf();

            $mock->shouldReceive('get')
                ->atLeast()
                ->once()
                ->with(['*'])
                ->andReturn(new Collection([
                    [
                        'id' => 2,
                        'name' => 'Name Two',
                    ],
                ]));
        });

        $criteria = static function (Builder $builder): void {
            $builder->where('id', '=', 2);
        };

        $paginator = new EloquentLengthAwarePaginator($this->mockModel($builder), new StartSizeData(1, 15));
        $paginator->setCriteria($criteria);

        self::assertCount(1, $paginator->getItems());
        self::assertEquals(1, $paginator->getTotalItems());
    }

    public function testGetItemsWithGetItemsCriteria(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $this->mock(Builder::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('count')
                ->atLeast()
                ->once()
                ->with('*')
                ->andReturn(1);

            $mock->shouldReceive('where')
                ->atLeast()
                ->once()
                ->with('id', '=', 2)
                ->andReturnSelf();

            $mock->shouldReceive('forPage')
                ->atLeast()
                ->once()
                ->with(1, 15)
                ->andReturnSelf();

            $mock->shouldReceive('get')
                ->atLeast()
                ->once()
                ->with(['*'])
                ->andReturn(new Collection([
                    [
                        'id' => 2,
                        'name' => 'Name Two',
                    ],
                ]));
        });

        $criteria = static function (Builder $builder): void {
            $builder->where('id', '=', 2);
        };

        $paginator = new EloquentLengthAwarePaginator($this->mockModel($builder), new StartSizeData(1, 15));
        $paginator->setGetItemsCriteria($criteria);

        self::assertCount(1, $paginator->getItems());
        self::assertEquals(1, $paginator->getTotalItems());
    }

    public function testGetTotalItems(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $this->mock(Builder::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('count')
                ->atLeast()
                ->once()
                ->with('column1,column2')
                ->andReturn(3);
        });

        $paginator = new EloquentLengthAwarePaginator($this->mockModel($builder), new StartSizeData(1, 15));
        $paginator->setSelect(['column1', 'column2']);
        $itemCount = $paginator->getTotalItems();

        self::assertEquals(3, $itemCount);
    }

    private function mockModel(Builder $builder): Model
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, static function (MockInterface $mock) use ($builder): void {
            $mock->shouldReceive('newQuery')
                ->atLeast()
                ->once()
                ->andReturn($builder);
        });

        return $model;
    }
}
