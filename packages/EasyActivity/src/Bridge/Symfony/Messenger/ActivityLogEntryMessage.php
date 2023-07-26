<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;

final class ActivityLogEntryMessage implements WithLockDataInterface
{
    private const LOCK_RESOURCE = 'activity_log_%s_%s';

    private const LOCK_TTL_SEC = 3600.0;

    public function __construct(
        private ActivityLogEntry $logEntry,
    ) {
    }

    public function getLockData(): LockDataInterface
    {
        $resource = \sprintf(
            self::LOCK_RESOURCE,
            $this->logEntry->getSubjectId(),
            $this->logEntry->getUpdatedAt()
                ->format('U.u')
        );

        return LockData::create($resource, self::LOCK_TTL_SEC);
    }

    public function getLogEntry(): ActivityLogEntry
    {
        return $this->logEntry;
    }
}
