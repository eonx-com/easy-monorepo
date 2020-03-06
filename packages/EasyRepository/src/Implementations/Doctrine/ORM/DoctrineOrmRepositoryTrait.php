<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

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
     * @return object[]
     */
    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function beginTransaction(): void
    {
        $this->manager->beginTransaction();
    }

    public function commit(): void
    {
        $this->manager->commit();
    }

    /**
     * @param object|object[] $object
     */
    public function delete($object): void
    {
        $this->callManagerMethodForObjects('remove', $object);
    }

    /**
     * @param int|string $identifier
     *
     * @return null|object
     */
    public function find($identifier)
    {
        return $this->repository->find($identifier);
    }

    public function flush(): void
    {
        $this->manager->flush();
    }

    public function rollback(): void
    {
        $this->manager->rollback();
    }

    /**
     * @param object|object[] $object The object or list of objects to save
     */
    public function save($object): void
    {
        $this->callManagerMethodForObjects('persist', $object);
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
            $this->manager->close();
            $this->rollback();

            throw $exception;
        }
    }

    protected function createQueryBuilder(?string $alias = null, ?string $indexBy = null): QueryBuilder
    {
        return $this->repository->createQueryBuilder($alias ?? $this->getEntityAlias(), $indexBy);
    }

    protected function getEntityAlias(): string
    {
        $exploded = \explode('\\', $this->repository->getClassName());

        return \strtolower(\substr($exploded[\count($exploded) - 1], 0, 1));
    }

    /**
     * @param object|object[] $objects
     */
    private function callManagerMethodForObjects(string $method, $objects): void
    {
        if (\is_array($objects) === false) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->manager->{$method}($object);
        }
    }
}
