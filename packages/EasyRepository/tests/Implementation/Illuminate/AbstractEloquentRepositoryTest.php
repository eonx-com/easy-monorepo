<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Illuminate;

use EonX\EasyRepository\Tests\AbstractTestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use stdClass;

final class AbstractEloquentRepositoryTest extends AbstractTestCase
{
    public function testDeleteUsesGivenModel(): void
    {
        $repo = $this->createEloquentRepository();

        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('delete')
                ->once()
                ->withNoArgs();
        });

        $repo->delete($model);

        $this->addToAssertionCount(1);
    }

    public function testFindUsesModelAndReturnExpectedValue(): void
    {
        $object = new stdClass();

        $repo = $this->createEloquentRepository(function (MockInterface $model) use ($object): void {
            $model->shouldReceive('find')
                ->once()
                ->with('identifier')
                ->andReturn($object);
        });

        $found = $repo->find('identifier');

        if ($found !== null) {
            self::assertEquals(\spl_object_hash($object), \spl_object_hash($found));
        }
    }

    public function testSaveUsesGivenModel(): void
    {
        $repo = $this->createEloquentRepository();

        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('save')
                ->once()
                ->withNoArgs();
        });

        $repo->save($model);

        $this->addToAssertionCount(1);
    }

    private function createEloquentRepository(?callable $expectations = null): EloquentRepositoryStub
    {
        return new EloquentRepositoryStub($expectations);
    }
}
