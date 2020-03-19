<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use EonX\EasyRepository\Implementations\Doctrine\ORM\AbstractPaginatedDoctrineOrmRepository;

final class PaginatedDoctrineOrmRepositoryStub extends AbstractPaginatedDoctrineOrmRepository
{
    protected function getEntityClass(): string
    {
        return 'my-entity-class';
    }
}
