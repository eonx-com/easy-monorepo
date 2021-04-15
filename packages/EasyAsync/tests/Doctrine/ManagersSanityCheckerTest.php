<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine;

use EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException;
use EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException;
use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Doctrine\Stubs\EntityManagerForSanityStub;
use EonX\EasyAsync\Tests\Doctrine\Stubs\ManagerRegistryStub;

final class ManagersSanityCheckerTest extends AbstractTestCase
{
    public function testEntityManagerClosed(): void
    {
        $this->expectException(DoctrineManagerClosedException::class);

        $registry = new ManagerRegistryStub([
            'default' => new EntityManagerForSanityStub(false),
        ]);

        (new ManagersSanityChecker($registry))->checkSanity();
    }

    public function testEntityManagerConnectionNotOk(): void
    {
        $this->expectException(DoctrineConnectionNotOkException::class);

        $registry = new ManagerRegistryStub([
            'default' => new EntityManagerForSanityStub(true),
        ]);

        (new ManagersSanityChecker($registry))->checkSanity();
    }
}
