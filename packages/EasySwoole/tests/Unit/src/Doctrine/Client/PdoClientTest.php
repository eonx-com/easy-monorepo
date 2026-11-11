<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Doctrine\Client;

use EonX\EasySwoole\Doctrine\Client\PdoClient;
use EonX\EasySwoole\Doctrine\ClientConfig\PdoClientConfig;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PDOException;
use ReflectionException;

final class PdoClientTest extends AbstractUnitTestCase
{
    public function testItSucceedsWithoutCredentialsInLog(): void
    {
        $handler = new TestHandler();
        $config = new PdoClientConfig(
            params: [
                'dbname' => 'app',
                'driver' => 'pdo_mysql',
                'driverOptions' => [],
                'host' => '127.0.0.1',
                'password' => 'db-password',
                'port' => 1,
                'user' => 'db-user',
            ],
            logger: new Logger('test', [$handler])
        );

        try {
            new PdoClient($config);
        } catch (PDOException|ReflectionException) {
        }

        $records = $handler->getRecords();
        self::assertSame(
            [
                'params' => [
                    'dbname' => 'app',
                    'driver' => 'pdo_mysql',
                    'host' => '127.0.0.1',
                    'port' => 1,
                ],
            ],
            $records[0]->context
        );
    }
}
