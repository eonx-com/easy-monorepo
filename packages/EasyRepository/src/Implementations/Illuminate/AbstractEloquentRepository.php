<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Implementations\Illuminate;

use Illuminate\Database\Eloquent\Model;
use StepTheFkUp\EasyRepository\Interfaces\ObjectRepositoryInterface;

abstract class AbstractEloquentRepository implements ObjectRepositoryInterface
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
     * Get the eloquent model to use.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getModel(): Model;
}
