<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine;

use StepTheFkUp\EasyRepository\Implementations\Doctrine\AbstractDoctrineOrmRepository;

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