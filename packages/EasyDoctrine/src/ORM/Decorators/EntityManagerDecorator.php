<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\ORM\Decorators;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use InvalidArgumentException;
use Throwable;

final class EntityManagerDecorator extends DoctrineEntityManagerDecorator
{
    /**
     * @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface
     */
    private $deferredEntityEventDispatcher;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcher,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $wrapped
    ) {
        parent::__construct($wrapped);

        $this->deferredEntityEventDispatcher = $deferredEntityEventDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function commit(): void
    {
        parent::commit();

        if ($this->getConnection()->getTransactionNestingLevel() === 0) {
            $this->deferredEntityEventDispatcher->dispatch();
        }
    }

    public function rollback(): void
    {
        $transactionNestingLevel = $this->getConnection()
            ->getTransactionNestingLevel();

        if ($transactionNestingLevel > 0) {
            parent::rollback();
        }

        $this->deferredEntityEventDispatcher->clear($transactionNestingLevel);
    }

    /**
     * @param callable $func
     *
     * @return mixed
     *
     * @throws \Throwable
     *
     * @deprecated
     */
    public function transactional($func)
    {
        if (\is_callable($func) === false) {
            throw new InvalidArgumentException('Expected argument of type "callable", got "' . \gettype($func) . '"');
        }

        return $this->wrapInTransaction($func);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Throwable
     */
    public function wrapInTransaction(callable $func)
    {
        $this->beginTransaction();

        try {
            $return = $func($this);

            $this->flush();
            $this->commit();

            return $return;
        } catch (Throwable $throwable) {
            // Dispatch event before calling close() or rollback() as they throw exception too
            $this->eventDispatcher->dispatch(new TransactionalExceptionEvent($throwable));

            if ($throwable instanceof ORMException || $throwable instanceof DBALException) {
                $this->close();
            }
            $this->rollback();

            throw $throwable;
        }
    }
}
