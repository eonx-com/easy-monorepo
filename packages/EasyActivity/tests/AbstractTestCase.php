<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Throwable;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected ?Throwable $thrownException = null;

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

    protected function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        return $this->resolvePropertyReflection($object, $propertyName)
            ->getValue($object);
    }

    protected function safeCall(Closure $func): void
    {
        try {
            $func();
        } catch (Throwable $exception) {
            $this->thrownException = $exception;
        }
    }

    private function resolvePropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        while (\property_exists($object, $propertyName) === false) {
            $object = \get_parent_class($object);

            if ($object === false) {
                throw new LogicException(\sprintf('The $%s property does not exist.', $propertyName));
            }
        }

        return new ReflectionProperty($object, $propertyName);
    }
}
