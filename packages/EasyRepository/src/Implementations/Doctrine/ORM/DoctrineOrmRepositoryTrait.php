<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Implementations\Doctrine\ORM;

use Closure;
use Doctrine\ORM\QueryBuilder;

trait DoctrineOrmRepositoryTrait
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $manager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * Get all the objects managed by the repository.
     *
     * @return object[]
     */
    public function all(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Starts a transaction on the underlying database connection.
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->manager->beginTransaction();
    }

    /**
     * Commits a transaction on the underlying database connection.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->manager->commit();
    }

    /**
     * Delete given object(s).
     *
     * @param object|object[] $object
     *
     * @return void
     */
    public function delete($object): void
    {
        $this->callManagerMethodForObjects('remove', $object);
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
        return $this->repository->find($identifier);
    }

    /**
     * Synchronise in-memory changes to database.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * Performs a rollback on the underlying database connection.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->manager->rollback();
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
        $this->callManagerMethodForObjects('persist', $object);
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
            $this->manager->close();
            $this->rollback();

            throw $exception;
        }
    }

    /**
     * Create query builder from ORM repository.
     *
     * @param null|string $alias
     * @param null|string $indexBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder(?string $alias = null, ?string $indexBy = null): QueryBuilder
    {
        return $this->repository->createQueryBuilder($alias ?? $this->getEntityAlias(), $indexBy);
    }

    /**
     * Get entity alias.
     *
     * @return string
     */
    protected function getEntityAlias(): string
    {
        $exploded = \explode('\\', $this->repository->getClassName());

        return \strtolower(\substr($exploded[\count($exploded) - 1], 0, 1));
    }

    /**
     * Call given method on the manager for given object(s).
     *
     * @param string $method
     * @param object|object[] $objects
     *
     * @return void
     */
    private function callManagerMethodForObjects(string $method, $objects): void
    {
        if (\is_array($objects) === false) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->manager->$method($object);
        }
    }
}

\class_alias(
    DoctrineOrmRepositoryTrait::class,
    'StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM\DoctrineOrmRepositoryTrait',
    false
);
