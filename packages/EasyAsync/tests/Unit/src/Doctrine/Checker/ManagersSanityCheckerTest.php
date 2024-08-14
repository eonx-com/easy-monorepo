<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Doctrine\Checker;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use EonX\EasyAsync\Doctrine\Exception\DoctrineConnectionNotOkException;
use EonX\EasyAsync\Doctrine\Exception\DoctrineManagerClosedException;
use EonX\EasyAsync\Tests\Stub\Connection\BrokenConnection;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;

final class ManagersSanityCheckerTest extends AbstractUnitTestCase
{
    use PrivatePropertyAccessTrait;

    public function testEntityManagerClosed(): void
    {
        self::getService(EntityManagerInterface::class)->close();
        $sut = self::getService(ManagersSanityChecker::class);
        $this->expectException(DoctrineManagerClosedException::class);

        $sut->checkSanity();
    }

    public function testEntityManagerConnectionNotOk(): void
    {
        $connection = self::getService(EntityManagerInterface::class)->getConnection();
        self::setPrivatePropertyValue($connection, '_conn', new BrokenConnection());
        $sut = self::getService(ManagersSanityChecker::class);
        $this->expectException(DoctrineConnectionNotOkException::class);

        $sut->checkSanity();
    }
}
