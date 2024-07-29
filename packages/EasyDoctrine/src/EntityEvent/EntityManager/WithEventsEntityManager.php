<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\EntityManager;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Decorator\EntityManagerDecorator as DoctrineEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\EntityEvent\Event\TransactionalExceptionEvent;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Throwable;

final class WithEventsEntityManager extends DoctrineEntityManagerDecorator
{
    public function __construct(
        private readonly DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcher,
        private readonly EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $decorated,
    ) {
        parent::__construct($decorated);
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
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Throwable
     */
    public function wrapInTransaction(callable $func): mixed
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
