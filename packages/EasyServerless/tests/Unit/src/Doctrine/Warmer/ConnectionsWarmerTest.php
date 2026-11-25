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
    public function testWarmClosesAllConnectionsWhenClosingConnectionFails(): void
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
            ->method('executeQuery');
        $failingConnection
            ->expects(self::once())
            ->method('close')
            ->willThrowException(new RuntimeException('__close_error__'));
        $nextConnection = $this->createMock(Connection::class);
        $nextConnection
            ->method('getDatabasePlatform')
            ->willReturn($platform);
        $nextConnection
            ->expects(self::once())
            ->method('executeQuery');
        $nextConnection
            ->expects(self::once())
            ->method('close');
        $connections = [
            'failing' => $failingConnection,
            'next' => $nextConnection,
        ];
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnectionNames')
            ->willReturn([
                'failing' => 'doctrine.dbal.failing_connection',
                'next' => 'doctrine.dbal.next_connection',
            ]);
        $managerRegistry
            ->method('getConnection')
            ->willReturnCallback(static fn (string $name): object => $connections[$name]);
        $sut = new ConnectionsWarmer($managerRegistry);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('__close_error__');

        $sut->warm();
    }

    public function testWarmClosesCreatedConnectionsAndThrowsWhenConnectionCannotBeCreated(): void
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform
            ->method('getDummySelectSQL')
            ->willReturn('SELECT 1');
        $firstConnection = $this->createMock(Connection::class);
        $firstConnection
            ->method('getDatabasePlatform')
            ->willReturn($platform);
        $firstConnection
            ->expects(self::once())
            ->method('executeQuery');
        $firstConnection
            ->expects(self::once())
            ->method('close');
        $nextConnection = $this->createMock(Connection::class);
        $nextConnection
            ->method('getDatabasePlatform')
            ->willReturn($platform);
        $nextConnection
            ->expects(self::once())
            ->method('executeQuery');
        $nextConnection
            ->expects(self::once())
            ->method('close');
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnectionNames')
            ->willReturn([
                'first' => 'doctrine.dbal.first_connection',
                'not_creatable' => 'doctrine.dbal.not_creatable_connection',
                'next' => 'doctrine.dbal.next_connection',
            ]);
        $managerRegistry
            ->method('getConnection')
            ->willReturnCallback(static fn (string $name): object => match ($name) {
                'first' => $firstConnection,
                'next' => $nextConnection,
                default => throw new RuntimeException('__creation_error__'),
            });
        $sut = new ConnectionsWarmer($managerRegistry);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('__creation_error__');

        $sut->warm();
    }

    public function testWarmQueriesAndClosesAllConnectionsWhenQueryFails(): void
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
            ->method('getDatabasePlatform')
            ->willReturn($platform);
        $nextConnection
            ->expects(self::once())
            ->method('executeQuery');
        $nextConnection
            ->expects(self::once())
            ->method('close');
        $connections = [
            'failing' => $failingConnection,
            'next' => $nextConnection,
        ];
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnectionNames')
            ->willReturn([
                'failing' => 'doctrine.dbal.failing_connection',
                'next' => 'doctrine.dbal.next_connection',
            ]);
        $managerRegistry
            ->method('getConnection')
            ->willReturnCallback(static fn (string $name): object => $connections[$name]);
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
        $connections = [
            'default' => $defaultConnection,
            'not_dbal' => new stdClass(),
            'other' => $otherConnection,
        ];
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getConnectionNames')
            ->willReturn([
                'default' => 'doctrine.dbal.default_connection',
                'not_dbal' => 'doctrine.dbal.not_dbal_connection',
                'other' => 'doctrine.dbal.other_connection',
            ]);
        $managerRegistry
            ->method('getConnection')
            ->willReturnCallback(static fn (string $name): object => $connections[$name]);
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
