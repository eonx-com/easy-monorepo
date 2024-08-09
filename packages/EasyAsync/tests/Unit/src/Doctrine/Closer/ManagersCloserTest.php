<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Doctrine\Closer;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Doctrine\Closer\ManagersCloser;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyTest\Common\Trait\LoggerTrait;

final class ManagersCloserTest extends AbstractUnitTestCase
{
    use LoggerTrait;

    public function testCloseNotEntityManagerInstance(): void
    {
        self::bootKernel(['environment' => 'not_supported_entity_manager']);
        $sut = self::getService(ManagersCloser::class);

        $sut->close();

        self::assertLoggerHasWarning(
            'Type "EonX\EasyAsync\Tests\Fixture\App\ObjectManager\NotSupportedObjectManager"'
            . ' for manager "not_supported" not supported by manager closer'
        );
    }

    public function testCloseSuccessful(): void
    {
        $connection = self::getService(EntityManagerInterface::class)->getConnection();
        $connection->getDatabase();
        $sut = self::getService(ManagersCloser::class);

        $sut->close();

        self::assertFalse($connection->isConnected());
    }
}
