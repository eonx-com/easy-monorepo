<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\ORM\Decorators;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use InvalidArgumentException;
use Throwable;

final class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
     */
    private $errorHandler;

    /**
     * @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        DeferredEntityEventDispatcherInterface $eventDispatcher,
        ErrorHandlerInterface $errorHandler,
        EntityManagerInterface $wrapped
    ) {
        parent::__construct($wrapped);

        $this->eventDispatcher = $eventDispatcher;
        $this->errorHandler = $errorHandler;
    }

    public function commit(): void
    {
        parent::commit();

        if ($this->getConnection()->getTransactionNestingLevel() === 0) {
            $this->eventDispatcher->dispatch();
        }
    }

    public function rollback(): void
    {
        $transactionNestingLevel = $this->getConnection()
            ->getTransactionNestingLevel();

        if ($transactionNestingLevel > 0) {
            parent::rollback();
        }

        $this->eventDispatcher->clear($transactionNestingLevel);
    }

    /**
     * @param callable $func
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transactional($func)
    {
        if (\is_callable($func) === false) {
            throw new InvalidArgumentException('Expected argument of type "callable", got "' . \gettype($func) . '"');
        }

        $this->beginTransaction();

        try {
            $return = $func($this);

            $this->flush();
            $this->commit();

            return $return;
        } catch (Throwable $exception) {
            // Report exception before calling close() or rollback() as they throw exception too
            $this->errorHandler->report($exception);

            if ($exception instanceof ORMException || $exception instanceof DBALException) {
                $this->close();
            }
            $this->rollback();

            throw $exception;
        }
    }
}
