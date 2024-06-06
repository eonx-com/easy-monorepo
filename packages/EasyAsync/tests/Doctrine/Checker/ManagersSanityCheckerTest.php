<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine\Checker;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException;
use EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException;
use EonX\EasyAsync\Tests\AbstractStoreTestCase;
use EonX\EasyAsync\Tests\Stub\ConnectionStub;
use EonX\EasyAsync\Tests\Stub\EntityManagerForSanityStub;
use EonX\EasyAsync\Tests\Stub\ManagerRegistryStub;

final class ManagersSanityCheckerTest extends AbstractStoreTestCase
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
            'default' => new EntityManagerForSanityStub(true, ConnectionStub::class),
        ]);

        (new ManagersSanityChecker($registry))->checkSanity();
    }

    public function testWithRealDoctrineConnection(): void
    {
        $config = new Configuration();
        $config->setMetadataDriverImpl(new PHPDriver(__DIR__));
        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace('proxies');

        $conn = $this->getDoctrineDbalConnection();
        $entityManager = EntityManager::create($conn, $config);

        $registry = new ManagerRegistryStub([
            'default' => $entityManager,
        ]);

        (new ManagersSanityChecker($registry))->checkSanity();

        self::assertTrue(true);
    }
}
