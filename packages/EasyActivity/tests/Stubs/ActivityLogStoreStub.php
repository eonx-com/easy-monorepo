<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class ActivityLogStoreStub implements StoreInterface
{
    public function getIdentifier(object $subject): ?string
    {
        return null;
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        return $logEntry;
    }
}
