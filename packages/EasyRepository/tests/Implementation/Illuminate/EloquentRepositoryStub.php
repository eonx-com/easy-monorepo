<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Illuminate;

use Illuminate\Database\Eloquent\Model;
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

        if ($this->modelExpectations !== null) {
            \call_user_func($this->modelExpectations, $mock);
        }

        return $mock;
    }
}
