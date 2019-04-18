<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine\ORM;

use StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM\AbstractPaginatedDoctrineOrmRepository;

final class PaginatedDoctrineOrmRepositoryStub extends AbstractPaginatedDoctrineOrmRepository
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
    PaginatedDoctrineOrmRepositoryStub::class,
    'LoyaltyCorp\EasyRepository\Tests\Implementation\Doctrine\ORM\PaginatedDoctrineOrmRepositoryStub',
    false
);
