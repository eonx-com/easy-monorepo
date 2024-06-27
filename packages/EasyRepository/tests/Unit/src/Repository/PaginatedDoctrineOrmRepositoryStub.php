<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Unit\Repository;

use EonX\EasyRepository\Repository\AbstractPaginatedDoctrineOrmRepository;
use stdClass;

final class PaginatedDoctrineOrmRepositoryStub extends AbstractPaginatedDoctrineOrmRepository
{
    protected function getEntityClass(): string
    {
        return stdClass::class;
    }
}
