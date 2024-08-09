<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\EntityManager;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\EntityEvent\Event\WrapInTransactionExceptionEvent;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Throwable;

final class WithEventsEntityManager extends EntityManagerDecorator
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
        parent::rollback();

        $this->deferredEntityEventDispatcher->clear($this->getConnection()->getTransactionNestingLevel());
    }

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
            $this->eventDispatcher->dispatch(new WrapInTransactionExceptionEvent($throwable));

            if ($throwable instanceof ORMException || $throwable instanceof DBALException) {
                $this->close();
            }
            $this->rollback();

            throw $throwable;
        }
    }
}
