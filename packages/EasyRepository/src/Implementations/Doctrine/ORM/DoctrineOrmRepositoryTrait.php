<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Closure;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Throwable;

trait DoctrineOrmRepositoryTrait
{
    protected EntityManagerInterface $manager;

    protected EntityRepository $repository;

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

    public function find(int|string $identifier): ?object
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
     * @throws \Throwable
     */
    public function transactional(Closure $func): mixed
    {
        $this->beginTransaction();

        try {
            $return = $func();

            $this->commit();

            return $return ?? true;
        } catch (Throwable $exception) {
            if ($exception instanceof ORMException || $exception instanceof Exception) {
                $this->manager->close();
            }

            $this->rollback();

            throw $exception;
        }
    }

    protected function createQueryBuilder(?string $alias = null, ?string $indexBy = null): QueryBuilder
    {
        return $this->repository->createQueryBuilder($alias ?? $this->getEntityAlias(), $indexBy);
    }

    protected function getClassMetadata(): ClassMetadata
    {
        return $this->manager->getClassMetadata($this->repository->getClassName());
    }

    protected function getEntityAlias(): string
    {
        $exploded = \explode('\\', $this->repository->getClassName());

        return \strtolower(\substr($exploded[\count($exploded) - 1], 0, 1));
    }

    /**
     * @param object|object[] $objects
     */
    private function callManagerMethodForObjects(string $method, array|object $objects): void
    {
        if (\is_array($objects) === false) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->manager->{$method}($object);
        }
    }
}
