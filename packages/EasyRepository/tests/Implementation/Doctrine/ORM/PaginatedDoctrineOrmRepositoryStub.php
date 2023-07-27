<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use EonX\EasyRepository\Implementations\Doctrine\ORM\AbstractPaginatedDoctrineOrmRepository;
use stdClass;

final class PaginatedDoctrineOrmRepositoryStub extends AbstractPaginatedDoctrineOrmRepository
{
    protected function getEntityClass(): string
    {
        return stdClass::class;
    }
}
