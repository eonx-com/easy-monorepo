<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Stub\Repository;

use EonX\EasyRepository\Repository\AbstractDoctrineOrmRepository;
use stdClass;

final class DoctrineOrmRepositoryStub extends AbstractDoctrineOrmRepository
{
    protected function getEntityClass(): string
    {
        return stdClass::class;
    }
}
