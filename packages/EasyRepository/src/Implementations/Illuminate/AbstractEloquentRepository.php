<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Illuminate;

use Closure;
use Illuminate\Database\Eloquent\Model;
use EonX\EasyRepository\Interfaces\DatabaseRepositoryInterface;

abstract class AbstractEloquentRepository implements DatabaseRepositoryInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * AbstractEloquentRepository constructor.
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Get all the objects managed by the repository.
     *
     * @return object[]
     */
    public function all(): array
    {
        return \array_values($this->model->all()->getDictionary());
    }

    /**
     * Starts a transaction on the underlying database connection.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beginTransaction(): void
    {
        $this->model->getConnection()->beginTransaction();
    }

    /**
     * Commits a transaction on the underlying database connection.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->model->getConnection()->commit();
    }

    /**
     * Delete given object(s).
     *
     * @param object|object[] $object
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete($object): void
    {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        /** @var \Illuminate\Database\Eloquent\Model $obj */
        foreach ($object as $obj) {
            $obj->delete();
        }
    }

    /**
     * Find object for given identifier, return null if not found.
     *
     * @param int|string $identifier
     *
     * @return null|object
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     */
    public function find($identifier)
    {
        return $this->model->find($identifier);
    }

    /**
     * Synchronise in-memory changes to database.
     *
     * @return void
     */
    public function flush(): void
    {
        // Feature not supported by eloquent.
    }

    /**
     * Performs a rollback on the underlying database connection.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function rollback(): void
    {
        $this->model->getConnection()->rollBack();
    }

    /**
     * Save given object(s).
     *
     * @param object|object[] $object The object or list of objects to save
     *
     * @return void
     */
    public function save($object): void
    {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        /** @var \Illuminate\Database\Eloquent\Model $obj */
        foreach ($object as $obj) {
            $obj->save();
        }
    }

    /**
     * Executes a function in a transaction.
     * If an exception occurs during execution of the function or flushing or transaction commit,
     * the transaction is rolled back, the EntityManager closed and the exception re-thrown.
     *
     * @param \Closure $func
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transactional(Closure $func)
    {
        $this->beginTransaction();

        try {
            $return = \call_user_func($func);

            $this->commit();

            return $return ?? true;
        } catch (\Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }

    /**
     * Get the eloquent model to use.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getModel(): Model;
}


