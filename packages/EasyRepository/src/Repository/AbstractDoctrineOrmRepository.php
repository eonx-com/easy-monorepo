<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use Doctrine\Persistence\ManagerRegistry;
use UnexpectedValueException;

abstract class AbstractDoctrineOrmRepository implements DatabaseRepositoryInterface
{
    use DoctrineOrmRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        $entityClass = $this->getEntityClass();
        /** @var \Doctrine\ORM\EntityManagerInterface|null $manager */
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new UnexpectedValueException(\sprintf('No manager found for entity class "%s".', $entityClass));
        }

        $this->manager = $manager;
        $this->repository = $this->manager->getRepository($entityClass);
    }

    /**
     * @return class-string
     */
    abstract protected function getEntityClass(): string;
}
