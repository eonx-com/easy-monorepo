<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\EntityManagers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyCore\Doctrine\EntityManagers\EntityManagerDecorator;
use EonX\EasyCore\Tests\AbstractTestCase;
use Exception;
use InvalidArgumentException;
use stdClass;

/**
 * @covers \EonX\EasyCore\Doctrine\EntityManagers\EntityManagerDecorator
 */
final class EntityManagerDecoratorTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testTransactionalThrowsExceptionAndClosesEntityManagerOnDoctrineExceptions
     */
    public function provideDoctrineExceptionClasses(): array
    {
        return [
            'DBAL exception' => [new DBALException()],
            'ORM exception' => [new ORMException()],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testTransactionalSucceeds
     */
    public function provideReturnValuesData(): array
    {
        return [
            'callable returns not null' => [
                'callableReturns' => 'some-value',
                'transactionalReturns' => 'some-value',
            ],
            'callable returns null' => [
                'callableReturns' => null,
                'transactionalReturns' => true,
            ],
        ];
    }

    /**
     * @param mixed $callableReturns
     * @param mixed $transactionalReturns
     *
     * @throws \Throwable
     *
     * @dataProvider provideReturnValuesData
     */
    public function testTransactionalSucceeds($callableReturns, $transactionalReturns): void
    {
        $spyForCallable = new stdClass();
        $spyForCallable->wasCalled = false;
        $callableArgument = static function ($arg) use ($spyForCallable, $callableReturns) {
            $spyForCallable->wasCalled = true;
            $spyForCallable->wasCalledWithArgument = $arg;

            return $callableReturns;
        };
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn(0);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityManagerDecorator = new EntityManagerDecorator($eventDispatcher->reveal(), $entityManager->reveal());

        $result = $entityManagerDecorator->transactional($callableArgument);

        $entityManager->beginTransaction()
            ->shouldHaveBeenCalledOnce();
        self::assertTrue($spyForCallable->wasCalled);
        self::assertSame($entityManagerDecorator, $spyForCallable->wasCalledWithArgument);
        /** @noinspection PhpMethodParametersCountMismatchInspection The null value is setting in the parent class */
        $entityManager->flush(null)
            ->shouldHaveBeenCalledOnce();
        $entityManager->commit()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $eventDispatcher->dispatch()
            ->shouldHaveBeenCalledOnce();
        self::assertSame($transactionalReturns, $result);
    }

    /**
     * @throws \Exception
     */
    public function testTransactionalThrowsException(): void
    {
        $exception = new Exception('some-exception-message');
        $callableArgument = static function () use ($exception): void {
            throw $exception;
        };
        $connection = $this->prophesize(Connection::class);
        $transactionNestingLevel = 1;
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityManagerDecorator = new EntityManagerDecorator($eventDispatcher->reveal(), $entityManager->reveal());

        $this->safeCall(static function () use ($entityManagerDecorator, $callableArgument): void {
            $entityManagerDecorator->transactional($callableArgument);
        });

        $this->assertThrownException(\Throwable::class, 0);
        $entityManager->beginTransaction()
            ->shouldHaveBeenCalledOnce();
        $entityManager->close()
            ->shouldNotHaveBeenCalled();
        $entityManager->rollback()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $eventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @param \Doctrine\DBAL\Exception|\Doctrine\ORM\ORMException $doctrineException
     *
     * @throws \Exception
     *
     * @dataProvider provideDoctrineExceptionClasses
     */
    public function testTransactionalThrowsExceptionAndClosesEntityManagerOnDoctrineExceptions($doctrineException): void
    {
        $callableArgument = static function () use ($doctrineException): void {
            throw $doctrineException;
        };
        $connection = $this->prophesize(Connection::class);
        $transactionNestingLevel = 1;
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityManagerDecorator = new EntityManagerDecorator($eventDispatcher->reveal(), $entityManager->reveal());

        $this->safeCall(static function () use ($entityManagerDecorator, $callableArgument): void {
            $entityManagerDecorator->transactional($callableArgument);
        });

        $this->assertThrownException(\get_class($doctrineException), 0);
        $entityManager->beginTransaction()
            ->shouldHaveBeenCalledOnce();
        $entityManager->close()
            ->shouldHaveBeenCalledOnce();
        $entityManager->rollback()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $eventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @throws \Exception
     */
    public function testTransactionalThrowsExceptionWhenArgumentNotCallable(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManagerDecorator = new EntityManagerDecorator(
            $this->prophesize(DeferredEntityEventDispatcherInterface::class)->reveal(),
            $entityManager->reveal()
        );

        $this->safeCall(static function () use ($entityManagerDecorator): void {
            /** @var callable $callableFake */
            $callableFake = 'non-callable-argument';
            $entityManagerDecorator->transactional($callableFake);
        });

        $this->assertThrownException(InvalidArgumentException::class, 0);
    }
}
