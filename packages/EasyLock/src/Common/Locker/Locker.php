<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\Locker;

use Closure;
use Doctrine\DBAL\Driver\PDO\Exception as PdoException;
use EonX\EasyAsync\Common\Exception\ShouldKillWorkerExceptionInterface;
use EonX\EasyLock\Common\Exception\LockAcquiringException;
use EonX\EasyLock\Common\Exception\ShouldRetryException;
use EonX\EasyLock\Common\ValueObject\LockData;
use Symfony\Component\Lock\Exception\LockAcquiringException as BaseLockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class Locker implements LockerInterface
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        return $this->lockFactory
            ->createLock($resource, $ttl ?? 300.0);
    }

    public function processWithLock(LockData $lockData, Closure $func): mixed
    {
        $lock = $this->createLock($lockData->getResource(), $lockData->getTtl());

        try {
            $lockAcquired = $lock->acquire();
        } catch (BaseLockAcquiringException $exception) {
            $previous = $exception->getPrevious();
            $easyAsyncInstalled = \interface_exists(ShouldKillWorkerExceptionInterface::class);
            $pdoExceptionExists = \class_exists(PdoException::class);

            // If eonx-com/easy-async installed, and previous is because SQL connection not ok, kill worker
            if ($easyAsyncInstalled
                && $pdoExceptionExists
                && $previous instanceof PdoException
                && $previous->getCode() === 0) {
                throw new LockAcquiringException($exception->getMessage(), $exception->getCode(), $previous);
            }

            throw $exception;
        }

        if ($lockAcquired === false) {
            // Throw exception to indicate we want ot retry
            if ($lockData->shouldRetry()) {
                throw new ShouldRetryException(\sprintf('Should retry "%s"', $lockData->getResource()));
            }

            return null;
        }

        try {
            return $func();
        } finally {
            $lock->release();
        }
    }
}
