<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Doctrine\Closer;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Doctrine\Closer\ConnectionCloser;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;

final class ConnectionCloserTest extends AbstractUnitTestCase
{
    public function testCloseSuccessful(): void
    {
        $connection = self::getService(EntityManagerInterface::class)->getConnection();
        $connection->getDatabase();
        $sut = self::getService(ConnectionCloser::class);

        $sut->close();

        self::assertFalse($connection->isConnected());
    }
}
