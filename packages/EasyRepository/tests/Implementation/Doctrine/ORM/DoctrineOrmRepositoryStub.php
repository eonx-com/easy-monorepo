<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Tests\Implementation\Doctrine\ORM;

use LoyaltyCorp\EasyRepository\Implementations\Doctrine\ORM\AbstractDoctrineOrmRepository;

final class DoctrineOrmRepositoryStub extends AbstractDoctrineOrmRepository
{
    /**
     * Get entity class managed by the repository.
     *
     * @return string
     */
    protected function getEntityClass(): string
    {
        return 'my-entity-class';
    }
}

\class_alias(
    DoctrineOrmRepositoryStub::class,
    'StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine\ORM\DoctrineOrmRepositoryStub',
    false
);
