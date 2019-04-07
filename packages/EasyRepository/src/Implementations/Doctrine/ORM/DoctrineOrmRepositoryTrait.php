<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

trait DoctrineOrmRepositoryTrait
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $manager;

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

        $this->manager->flush();
    }
}
