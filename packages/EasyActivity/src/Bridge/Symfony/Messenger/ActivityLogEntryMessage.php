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

    /**
     * @var \EonX\EasyActivity\ActivityLogEntry
     */
    private $logEntry;

    public function __construct(ActivityLogEntry $logEntry)
    {
        $this->logEntry = $logEntry;
    }

    public function getLogEntry(): ActivityLogEntry
    {
        return $this->logEntry;
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
}
