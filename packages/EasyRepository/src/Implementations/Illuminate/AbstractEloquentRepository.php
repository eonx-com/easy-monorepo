<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Illuminate;

use Closure;
use EonX\EasyRepository\Interfaces\DatabaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

abstract class AbstractEloquentRepository implements DatabaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * @return object[]
     */
    public function all(): array
    {
        return \array_values($this->model->all()->getDictionary());
    }

    public function beginTransaction(): void
    {
        $this->model->getConnection()
            ->beginTransaction();
    }

    public function commit(): void
    {
        $this->model->getConnection()
            ->commit();
    }

    /**
     * @param object|object[] $object
     *
     * @throws \Exception
     */
    public function delete(array|object $object): void
    {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        /** @var \Illuminate\Database\Eloquent\Model $obj */
        foreach ($object as $obj) {
            $obj->delete();
        }
    }

    public function find(int|string $identifier): ?object
    {
        return $this->model->find($identifier);
    }

    public function flush(): void
    {
        // Feature not supported by eloquent
    }

    public function rollback(): void
    {
        $this->model->getConnection()
            ->rollBack();
    }

    /**
     * @param object|object[] $object The object or list of objects to save
     */
    public function save(array|object $object): void
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
     * @throws \Throwable
     */
    public function transactional(Closure $func): mixed
    {
        $this->beginTransaction();

        try {
            $return = \call_user_func($func);

            $this->commit();

            return $return ?? true;
        } catch (Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }

    abstract protected function getModel(): Model;
}
