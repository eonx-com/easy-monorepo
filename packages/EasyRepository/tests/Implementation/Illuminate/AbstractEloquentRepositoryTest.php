<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Illuminate;

use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use StepTheFkUp\EasyRepository\Tests\AbstractTestCase;

final class AbstractEloquentRepositoryTest extends AbstractTestCase
{
    /**
     * Repository should use all() method on internal eloquent model.
     *
     * @return void
     */
    public function testAllUsesModelAndReturnArray(): void
    {
        $repo = $this->createEloquentRepository(function (MockInterface $model, MockInterface $collection): void {
            $collection->shouldReceive('getDictionary')->once()->withNoArgs()->andReturn([]);
            $model->shouldReceive('all')->once()->withNoArgs()->andReturn($collection);
        });

        self::assertEquals([], $repo->all());
    }

    /**
     * Repository should use delete() method on the given eloquent model instead of the internal one.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testDeleteUsesGivenModel(): void
    {
        $repo = $this->createEloquentRepository();

        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('delete')->once()->withNoArgs();
        });

        $repo->delete($model);

        $this->addToAssertionCount(1);
    }

    /**
     * Repository should use find() method on internal model and return identical return value.
     *
     * @return void
     */
    public function testFindUsesModelAndReturnExpectedValue(): void
    {
        $object = new \stdClass();

        $repo = $this->createEloquentRepository(function (MockInterface $model) use ($object): void {
            $model->shouldReceive('find')->once()->with('identifier')->andReturn($object);
        });

        $found = $repo->find('identifier');

        if ($found !== null) {
            self::assertEquals(\spl_object_hash($object), \spl_object_hash($found));
        }
    }

    /**
     * Repository should use save() method on the given eloquent model instead of the internal one.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testSaveUsesGivenModel(): void
    {
        $repo = $this->createEloquentRepository();

        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('save')->once()->withNoArgs();
        });

        $repo->save($model);

        $this->addToAssertionCount(1);
    }

    /**
     * Create eloquent repository stub for given mock expectations.
     *
     * @param null|callable $expectations
     *
     * @return \StepTheFkUp\EasyRepository\Tests\Implementation\Illuminate\EloquentRepositoryStub
     */
    private function createEloquentRepository(?callable $expectations = null): EloquentRepositoryStub
    {
        return new EloquentRepositoryStub($expectations);
    }
}

\class_alias(
    AbstractEloquentRepositoryTest::class,
    'LoyaltyCorp\EasyRepository\Tests\Implementation\Illuminate\AbstractEloquentRepositoryTest',
    false
);
