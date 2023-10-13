<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;

    protected ?Throwable $thrownException = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected function assertLogEntries(EntityManagerInterface $entityManager, array $expectedLogEntries): void
    {
        $logEntries = $this->getLogEntries($entityManager);

        self::assertEquals($expectedLogEntries, $logEntries);
    }

    /**
     * @throws \Exception
     */
    protected function assertThrownException(
        string $expectedException,
        int $code,
        ?string $previousException = null,
    ): void {
        self::assertNotNull($this->thrownException);

        if ($this->thrownException instanceof $expectedException === false) {
            throw $this->thrownException;
        }

        self::assertSame($code, $this->thrownException->getCode());

        if ($previousException === null) {
            self::assertNull($this->thrownException->getPrevious());
        }

        if ($previousException !== null) {
            self::assertTrue($this->thrownException->getPrevious() instanceof $previousException);
        }
    }

    protected function getLogEntries(EntityManagerInterface $entityManager): array
    {
        $sql = \sprintf('SELECT * FROM %s', EntityManagerStub::ACTIVITY_TABLE_NAME);

        $logEntries = $entityManager->getConnection()
            ->fetchAllAssociative($sql);
        foreach ($logEntries as $key => $logEntry) {
            unset($logEntries[$key]['id']);
        }

        return $logEntries;
    }

    protected function safeCall(Closure $func): void
    {
        try {
            $func();
        } catch (Throwable $exception) {
            $this->thrownException = $exception;
        }
    }
}
