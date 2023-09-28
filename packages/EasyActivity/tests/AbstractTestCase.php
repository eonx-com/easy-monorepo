<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\Tests\Stubs\EntityManagerStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \Throwable|null
     */
    protected $thrownException = null;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param array<int, array<string, mixed>> $expectedLogEntries
     */
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

        if ($this->thrownException === null) {
            return;
        }

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

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * Returns object's private property value.
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getPrivatePropertyValue($object, string $property)
    {
        return (function ($property) {
            return $this->{$property};
        })->call($object, $property);
    }

    protected function safeCall(Closure $func): void
    {
        try {
            $func();
        } catch (\Throwable $exception) {
            $this->thrownException = $exception;
        }
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $files = [__DIR__ . '/../var'];

        foreach ($files as $file) {
            if ($fs->exists($file)) {
                $fs->remove($file);
            }
        }

        parent::tearDown();
    }
}
