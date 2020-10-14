<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Illuminate;

use Closure;
use EonX\EasyRepository\Interfaces\DatabaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractEloquentRepository implements DatabaseRepositoryInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

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
     * @param int|string $identifier
     *
     * @return null|object
     */
    public function find($identifier)
    {
        return $this->model->find($identifier);
    }

    public function flush(): void
    {
        // Feature not supported by eloquent.
    }

    public function rollback(): void
    {
        $this->model->getConnection()
            ->rollBack();
    }

    /**
     * @param object|object[] $object The object or list of objects to save
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

    abstract protected function getModel(): Model;
}
