<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Messenger\Message;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use EonX\EasyLock\Common\ValueObject\WithLockDataInterface;

final readonly class ActivityLogEntryMessage implements WithLockDataInterface
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
