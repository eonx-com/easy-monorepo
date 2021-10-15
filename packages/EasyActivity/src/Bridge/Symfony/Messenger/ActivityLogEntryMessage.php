<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Messenger;

use EonX\EasyActivity\ActivityLogEntry;

final class ActivityLogEntryMessage
{
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
}
