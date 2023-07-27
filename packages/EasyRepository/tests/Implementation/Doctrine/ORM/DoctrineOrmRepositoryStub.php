<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use EonX\EasyRepository\Implementations\Doctrine\ORM\AbstractDoctrineOrmRepository;
use stdClass;

final class DoctrineOrmRepositoryStub extends AbstractDoctrineOrmRepository
{
    protected function getEntityClass(): string
    {
        return stdClass::class;
    }
}
