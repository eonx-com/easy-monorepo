<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\ORM\Decorators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Prophecy\Argument;
use stdClass;
use Throwable;

#[CoversClass(EntityManagerDecorator::class)]
final class EntityManagerDecoratorTest extends AbstractTestCase
{
    /**
     * @see testWrapInTransactionThrowsExceptionAndClosesEntityManagerOnDoctrineExceptions
     */
    public static function provideDoctrineExceptionClasses(): iterable
    {
        yield 'DBAL exception' => [new DBALException()];
        yield 'ORM exception' => [new ORMException()];
    }

    /**
     * @see testWrapInTransactionSucceeds
     */
    public static function provideReturnValuesData(): iterable
    {
        yield 'callable returns not null' => [
            'callableReturns' => 'some-value',
            'transactionalReturns' => 'some-value',
        ];
        yield 'callable returns null' => [
            'callableReturns' => null,
            'transactionalReturns' => null,
        ];
    }

    public function testCommitSucceedsWithTransactionNestingLevel(): void
    {
        $transactionNestingLevel = 1;
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $entityEventDispatcherReveal */
        $entityEventDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $entityEventDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $entityManagerDecorator->commit();

        $entityManager->commit()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->dispatch()
            ->shouldNotBeCalled();
    }

    public function testCommitSucceedsWithoutTransactionNestingLevel(): void
    {
        $transactionNestingLevel = 0;
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $entityManagerDecorator->commit();

        $entityManager->commit()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->dispatch()
            ->shouldHaveBeenCalledOnce();
    }

    public function testRollbackSucceedsWithTransactionNestingLevel(): void
    {
        $transactionNestingLevel = 1;
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $entityManagerDecorator->rollback();

        $entityManager->rollback()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }

    public function testRollbackSucceedsWithoutTransactionNestingLevel(): void
    {
        $transactionNestingLevel = 0;
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $entityManagerDecorator->rollback();

        $entityManager->rollback()
            ->shouldNotHaveBeenCalled();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('provideReturnValuesData')]
    public function testWrapInTransactionSucceeds(mixed $callableReturns, mixed $transactionalReturns): void
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
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $result = $entityManagerDecorator->wrapInTransaction($callableArgument);

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
        $deferredEntityEventDispatcher->dispatch()
            ->shouldHaveBeenCalledOnce();
        self::assertSame($transactionalReturns, $result);
    }

    /**
     * @throws \Exception
     */
    public function testWrapInTransactionThrowsException(): void
    {
        $exception = new Exception('some-exception-message');
        $callableArgument = static function () use ($exception): never {
            throw $exception;
        };
        $connection = $this->prophesize(Connection::class);
        $transactionNestingLevel = 1;
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::type(TransactionalExceptionEvent::class))
            ->willReturnArgument();
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $this->safeCall(static function () use ($entityManagerDecorator, $callableArgument): void {
            $entityManagerDecorator->wrapInTransaction($callableArgument);
        });

        $this->assertThrownException(Throwable::class, 0);
        $entityManager->beginTransaction()
            ->shouldHaveBeenCalledOnce();
        $entityManager->close()
            ->shouldNotHaveBeenCalled();
        $eventDispatcher->dispatch(new TransactionalExceptionEvent($exception))
            ->shouldHaveBeenCalledOnce();
        $entityManager->rollback()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }

    /**
     * @param \Doctrine\DBAL\Exception|\Doctrine\ORM\ORMException $doctrineException
     *
     * @throws \Exception
     */
    #[DataProvider('provideDoctrineExceptionClasses')]
    public function testWrapInTransactionThrowsExceptionAndClosesEntityManagerOnDoctrineExceptions(
        $doctrineException,
    ): void {
        $callableArgument = static function () use ($doctrineException): never {
            throw $doctrineException;
        };
        $connection = $this->prophesize(Connection::class);
        $transactionNestingLevel = 1;
        $connection->getTransactionNestingLevel()
            ->willReturn($transactionNestingLevel);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManagerReveal */
        $entityManagerReveal = $entityManager->reveal();
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredDispatcherReveal */
        $deferredDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::type(TransactionalExceptionEvent::class))
            ->willReturnArgument();
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $entityManagerDecorator = new EntityManagerDecorator(
            $deferredDispatcherReveal,
            $eventDispatcherReveal,
            $entityManagerReveal
        );

        $this->safeCall(static function () use ($entityManagerDecorator, $callableArgument): void {
            $entityManagerDecorator->wrapInTransaction($callableArgument);
        });

        $this->assertThrownException($doctrineException::class, 0);
        $entityManager->beginTransaction()
            ->shouldHaveBeenCalledOnce();
        $entityManager->close()
            ->shouldHaveBeenCalledOnce();
        $eventDispatcher->dispatch(new TransactionalExceptionEvent($doctrineException))
            ->shouldHaveBeenCalledOnce();
        $entityManager->rollback()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->clear($transactionNestingLevel)
            ->shouldHaveBeenCalledOnce();
    }
}
