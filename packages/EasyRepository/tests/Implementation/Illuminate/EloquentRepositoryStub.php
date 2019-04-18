<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Illuminate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use StepTheFkUp\EasyRepository\Implementations\Illuminate\AbstractEloquentRepository;

final class EloquentRepositoryStub extends AbstractEloquentRepository
{
    /**
     * @var null|callable
     */
    private $modelExpectations;

    /**
     * EloquentRepositoryStub constructor.
     *
     * @param null|callable $modelExpectations
     */
    public function __construct(?callable $modelExpectations = null)
    {
        $this->modelExpectations = $modelExpectations;

        parent::__construct();
    }

    /**
     * Get the eloquent model to use.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel(): Model
    {
        $mock = \Mockery::mock(Model::class);
        $collection = \Mockery::mock(Collection::class);

        if ($this->modelExpectations !== null) {
            \call_user_func($this->modelExpectations, $mock, $collection);
        }

        return $mock;
    }
}

\class_alias(
    EloquentRepositoryStub::class,
    'LoyaltyCorp\EasyRepository\Tests\Implementation\Illuminate\EloquentRepositoryStub',
    false
);
