<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Doctrine\Warmer;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyServerless\Doctrine\Warmer\ConnectionsWarmer;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use RuntimeException;
use stdClass;

final class ConnectionsWarmerTest extends AbstractUnitTestCase
{
    public function testWarmClosesAllConnectionsWhenQueryFails(): void
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform
            ->method('getDummySelectSQL')
            ->willReturn('SELECT 1');
        $failingConnection = $this->createMock(Connection::class);
        $failingConnection
            ->method('getDatabasePlatform')
            ->willReturn($platform);
        $failingConnection
            ->expects(self::once())
            ->method('executeQuery')
            ->willThrowException(new RuntimeException('__error__'));
        $failingConnection
            ->expects(self::once())
            ->method('close');
        $nextConnection = $this->createMock(Connection::class);
        $nextConnection
            ->expects(self::never())
            ->method('executeQuery');
        $nextConnection
            ->expects(self::once())
            ->method('close');
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnections')
            ->willReturn([
                'failing' => $failingConnection,
                'next' => $nextConnection,
            ]);
        $sut = new ConnectionsWarmer($managerRegistry);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('__error__');

        $sut->warm();
    }

    public function testWarmQueriesAndClosesConnections(): void
    {
        $defaultTestHandler = new TestHandler();
        $defaultConnection = DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            (new Configuration())->setMiddlewares([new Middleware(new Logger('default', [$defaultTestHandler]))])
        );
        $otherTestHandler = new TestHandler();
        $otherConnection = DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            (new Configuration())->setMiddlewares([new Middleware(new Logger('other', [$otherTestHandler]))])
        );
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnections')
            ->willReturn([
                'default' => $defaultConnection,
                'not_dbal' => new stdClass(),
                'other' => $otherConnection,
            ]);
        $sut = new ConnectionsWarmer($managerRegistry);
        $expectedLogMessages = ['Connecting with parameters {params}', 'Executing query: {sql}', 'Disconnecting'];
        $expectedQueryContext = ['sql' => 'SELECT 1'];

        $sut->warm();

        self::assertFalse($defaultConnection->isConnected());
        self::assertFalse($otherConnection->isConnected());
        self::assertSame(
            $expectedLogMessages,
            \array_map(static fn (LogRecord $record): string => $record->message, $defaultTestHandler->getRecords())
        );
        self::assertSame(
            $expectedLogMessages,
            \array_map(static fn (LogRecord $record): string => $record->message, $otherTestHandler->getRecords())
        );
        self::assertSame($expectedQueryContext, $defaultTestHandler->getRecords()[1]->context);
        self::assertSame($expectedQueryContext, $otherTestHandler->getRecords()[1]->context);
    }
}
