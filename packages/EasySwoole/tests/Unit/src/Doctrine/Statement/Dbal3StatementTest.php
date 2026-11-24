<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Doctrine\Statement;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use EonX\EasySwoole\Doctrine\Statement\Dbal3Statement;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use OpenSwoole\Core\Coroutine\Client\PDOClient;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDO;
use PHPUnit\Framework\Attributes\RequiresMethod;

#[RequiresMethod(Statement::class, 'bindParam')]
final class Dbal3StatementTest extends AbstractUnitTestCase
{
    public function testItExecutesWithBoundValue(): void
    {
        $sut = new Dbal3Statement(self::prepare('SELECT ? AS value'));
        $sut->bindValue(1, 'bound-value', ParameterType::STRING);

        $result = $sut->execute();

        self::assertSame('bound-value', $result->fetchOne());
    }

    public function testItExecutesWithParams(): void
    {
        $sut = new Dbal3Statement(self::prepare('SELECT ? AS value'));

        $result = $sut->execute(['param-value']);

        self::assertSame('param-value', $result->fetchOne());
    }

    private static function prepare(string $sql): PDOStatementProxy
    {
        $pdo = new PDO('sqlite::memory:');
        $client = new class ($pdo) extends PDOClient {
            protected function makeClient(): void
            {
                $this->__object = $this->config;
            }
        };
        $statement = $pdo->prepare($sql);
        self::assertNotFalse($statement);

        return new PDOStatementProxy($statement, $client);
    }
}
