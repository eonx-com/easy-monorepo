<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Stub\Repository;

use Closure;
use EonX\EasyRepository\Repository\AbstractEloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mockery;

final class EloquentRepositoryStub extends AbstractEloquentRepository
{
    private readonly ?Closure $modelExpectations;

    public function __construct(?callable $modelExpectations = null)
    {
        $this->modelExpectations = $modelExpectations === null ? null : $modelExpectations(...);

        parent::__construct();
    }

    protected function getModel(): Model
    {
        /** @var \Illuminate\Database\Eloquent\Model $mock */
        $mock = Mockery::mock(Model::class);
        $collection = Mockery::mock(Collection::class);

        if ($this->modelExpectations !== null) {
            \call_user_func($this->modelExpectations, $mock, $collection);
        }

        return $mock;
    }
}
